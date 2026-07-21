<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use Doctrine\DBAL\ParameterType;
use RuntimeException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('hgonTemplateNewsSlugMigration')]
final class HgonTemplateNewsSlugMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const NEWS_TABLE = 'tx_news_domain_model_news';
    private const SLUG_FIELD = 'path_segment';
    private const REALURL_TABLE = 'tx_realurl_uniqalias';
    private const DETAIL_PAGE_UID = 62;
    private const CURRENT_DETAIL_PAGE_SLUG = '/entdecken/detail';
    private const LEGACY_DETAIL_PAGE_SLUG = '/entdecken/aktuelles';

    public function getTitle(): string
    {
        return 'HGON Template: langlebige News-URLs wiederherstellen';
    }

    public function getDescription(): string
    {
        return 'Übernimmt die dauerhaften News-Aliase aus der früheren RealURL-Tabelle, erzeugt nur für '
            . 'verbliebene News eindeutige Pfadsegmente und stellt den Detailseitenpfad /entdecken/aktuelles '
            . 'wieder her. Bereits korrekte Pfadsegmente bleiben unverändert.';
    }

    public function executeUpdate(): bool
    {
        $newsRecords = $this->findNewsRecords();
        $targetSlugs = $this->buildTargetSlugs($newsRecords);
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::NEWS_TABLE);

        $connection->transactional(function (Connection $connection) use ($newsRecords, $targetSlugs): void {
            foreach ($newsRecords as $newsRecord) {
                $uid = (int)$newsRecord['uid'];
                $targetSlug = $targetSlugs[$uid];
                if ((string)($newsRecord[self::SLUG_FIELD] ?? '') === $targetSlug) {
                    continue;
                }

                $affectedRows = $connection->update(
                    self::NEWS_TABLE,
                    [self::SLUG_FIELD => $targetSlug],
                    ['uid' => $uid, 'deleted' => 0],
                    [self::SLUG_FIELD => ParameterType::STRING, 'uid' => ParameterType::INTEGER, 'deleted' => ParameterType::INTEGER]
                );
                if ($affectedRows !== 1) {
                    throw new RuntimeException(sprintf('Das Pfadsegment der News %d konnte nicht gespeichert werden.', $uid));
                }
            }

            $queryBuilder = $connection->createQueryBuilder();
            $queryBuilder
                ->update('pages')
                ->set('slug', self::LEGACY_DETAIL_PAGE_SLUG, true, ParameterType::STRING)
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter(self::DETAIL_PAGE_UID, ParameterType::INTEGER)),
                    $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter(self::CURRENT_DETAIL_PAGE_SLUG, ParameterType::STRING)),
                    $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
                )
                ->executeStatement();
        });

        return true;
    }

    public function updateNecessary(): bool
    {
        if ($this->detailPageSlugNeedsMigration()) {
            return true;
        }
        $newsRecords = $this->findNewsRecords();
        $targetSlugs = $this->buildTargetSlugs($newsRecords);
        foreach ($newsRecords as $newsRecord) {
            if ((string)($newsRecord[self::SLUG_FIELD] ?? '') !== $targetSlugs[(int)$newsRecord['uid']]) {
                return true;
            }
        }

        return false;
    }

    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }

    /** @return list<array<string, mixed>> */
    private function findNewsRecords(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::NEWS_TABLE);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->select('uid', 'pid', 'title', self::SLUG_FIELD, 'sys_language_uid')
            ->from(self::NEWS_TABLE)
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /** @param list<array<string, mixed>> $newsRecords @return array<int, string> */
    private function buildTargetSlugs(array $newsRecords): array
    {
        $legacyAliases = $this->findPermanentLegacyAliases();
        $fieldConfig = $GLOBALS['TCA'][self::NEWS_TABLE]['columns'][self::SLUG_FIELD]['config'] ?? null;
        if (!is_array($fieldConfig)) {
            throw new RuntimeException('Die TCA-Konfiguration für News-Pfadsegmente ist nicht verfügbar.');
        }
        $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, self::NEWS_TABLE, self::SLUG_FIELD, $fieldConfig);

        usort($newsRecords, static function (array $left, array $right) use ($legacyAliases): int {
            $leftHasAlias = isset($legacyAliases[(int)$left['uid']]);
            $rightHasAlias = isset($legacyAliases[(int)$right['uid']]);
            return $leftHasAlias === $rightHasAlias
                ? (int)$left['uid'] <=> (int)$right['uid']
                : ($leftHasAlias ? -1 : 1);
        });

        $claimed = [];
        $result = [];
        foreach ($newsRecords as $newsRecord) {
            $uid = (int)$newsRecord['uid'];
            $languageId = (int)$newsRecord['sys_language_uid'];
            $slug = $legacyAliases[$uid]
                ?? trim((string)($newsRecord[self::SLUG_FIELD] ?? ''));
            $slug = $slug !== ''
                ? $slugHelper->sanitize($slug)
                : $slugHelper->generate($newsRecord, (int)$newsRecord['pid']);
            $claimKey = $languageId . "\0" . $slug;
            if ($slug === '' || isset($claimed[$claimKey])) {
                $slug = $slugHelper->sanitize(($slug !== '' ? $slug : 'news') . '-' . $uid);
                $claimKey = $languageId . "\0" . $slug;
            }
            if ($slug === '' || isset($claimed[$claimKey])) {
                throw new RuntimeException(sprintf('Für die News %d konnte kein eindeutiges Pfadsegment erzeugt werden.', $uid));
            }
            $claimed[$claimKey] = true;
            $result[$uid] = $slug;
        }

        return $result;
    }

    /** @return array<int, string> */
    private function findPermanentLegacyAliases(): array
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::REALURL_TABLE);
        if (!in_array(self::REALURL_TABLE, $connection->createSchemaManager()->listTableNames(), true)) {
            return [];
        }
        $queryBuilder = $connection->createQueryBuilder();
        $rows = $queryBuilder
            ->select('value_id', 'value_alias')
            ->from(self::REALURL_TABLE)
            ->where(
                $queryBuilder->expr()->eq('tablename', $queryBuilder->createNamedParameter(self::NEWS_TABLE, ParameterType::STRING)),
                $queryBuilder->expr()->eq('field_id', $queryBuilder->createNamedParameter('uid', ParameterType::STRING)),
                $queryBuilder->expr()->eq('expire', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();

        $result = [];
        foreach ($rows as $row) {
            $alias = trim((string)$row['value_alias']);
            if ($alias !== '') {
                $result[(int)$row['value_id']] = $alias;
            }
        }
        return $result;
    }

    private function detailPageSlugNeedsMigration(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        return (bool)$queryBuilder
            ->count('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter(self::DETAIL_PAGE_UID, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter(self::CURRENT_DETAIL_PAGE_SLUG, ParameterType::STRING)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->executeQuery()
            ->fetchOne();
    }
}
