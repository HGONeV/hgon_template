<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use Doctrine\DBAL\ParameterType;
use RuntimeException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('hgonTemplateWorkGroupSlugMigration')]
final class HgonTemplateWorkGroupSlugMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const TABLE = 'tx_hgonworkgroup_domain_model_workgroup';
    private const SLUG_FIELD = 'slug';
    private const REALURL_TABLE = 'tx_realurl_uniqalias';
    private const STORAGE_PAGE_UID = 42;
    private const DETAIL_PAGE_UID = 265;
    private const CURRENT_DETAIL_PAGE_SLUG = '/arbeitskreise/detailansicht';
    private const LEGACY_DETAIL_PAGE_SLUG = '/arbeitskreise/arbeitskreis';

    public function getTitle(): string
    {
        return 'HGON Template: langlebige Arbeitskreis-URLs wiederherstellen';
    }

    public function getDescription(): string
    {
        return 'Übernimmt nach Möglichkeit die dauerhaften Arbeitskreis-Aliase aus der früheren RealURL-Tabelle, '
            . 'erzeugt für die übrigen Datensätze eindeutige Slugs und stellt den Detailseitenpfad '
            . '/arbeitskreise/arbeitskreis wieder her. Bereits vorhandene Slugs und abweichend konfigurierte '
            . 'Seitenpfade bleiben unverändert.';
    }

    public function executeUpdate(): bool
    {
        if (!$this->slugColumnExists()) {
            throw new RuntimeException('Die Datenbankspalte für Arbeitskreis-Slugs fehlt. Bitte zuerst das Datenbankschema aktualisieren.');
        }

        $workGroups = $this->findRecordsWithoutSlug();
        $legacyAliases = $this->findPermanentLegacyAliases();
        $fieldConfig = $GLOBALS['TCA'][self::TABLE]['columns'][self::SLUG_FIELD]['config'] ?? null;
        if ($workGroups !== [] && !is_array($fieldConfig)) {
            throw new RuntimeException('Die TCA-Konfiguration für Arbeitskreis-Slugs ist nicht verfügbar.');
        }

        $slugHelper = is_array($fieldConfig)
            ? GeneralUtility::makeInstance(SlugHelper::class, self::TABLE, self::SLUG_FIELD, $fieldConfig)
            : null;
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE);

        $connection->transactional(function (Connection $connection) use ($workGroups, $legacyAliases, $slugHelper): void {
            foreach ($workGroups as $workGroup) {
                $uid = (int)$workGroup['uid'];
                $state = RecordStateFactory::forName(self::TABLE)->fromArray($workGroup);
                $slug = isset($legacyAliases[$uid])
                    ? $slugHelper->sanitize($legacyAliases[$uid])
                    : $slugHelper->generate($workGroup, (int)$workGroup['pid']);

                if ($slug === '' || !$slugHelper->isUniqueInPid($slug, $state)) {
                    $fallbackRecord = $workGroup;
                    $fallbackRecord['title'] = rtrim((string)$workGroup['title']) . '-' . $uid;
                    $slug = $slugHelper->generate($fallbackRecord, (int)$workGroup['pid']);
                }
                if ($slug === '' || !$slugHelper->isUniqueInPid($slug, $state)) {
                    throw new RuntimeException(sprintf(
                        'Für den Arbeitskreis %d konnte kein eindeutiger Slug erzeugt werden.',
                        $uid
                    ));
                }

                $queryBuilder = $connection->createQueryBuilder();
                $affectedRows = $queryBuilder
                    ->update(self::TABLE)
                    ->set(self::SLUG_FIELD, $slug, true, ParameterType::STRING)
                    ->where(
                        $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, ParameterType::INTEGER)),
                        $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter(self::STORAGE_PAGE_UID, ParameterType::INTEGER)),
                        $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                        $this->emptySlugConstraint($queryBuilder)
                    )
                    ->executeStatement();
                if ($affectedRows !== 1) {
                    throw new RuntimeException(sprintf('Der Slug des Arbeitskreises %d konnte nicht gespeichert werden.', $uid));
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
        return !$this->slugColumnExists()
            || $this->findRecordsWithoutSlug() !== []
            || $this->detailPageSlugNeedsMigration();
    }

    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }

    /** @return list<array<string, mixed>> */
    private function findRecordsWithoutSlug(): array
    {
        if (!$this->slugColumnExists()) {
            return [];
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->select('*')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter(self::STORAGE_PAGE_UID, ParameterType::INTEGER)),
                $this->emptySlugConstraint($queryBuilder)
            )
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();
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
                $queryBuilder->expr()->eq('tablename', $queryBuilder->createNamedParameter(self::TABLE, ParameterType::STRING)),
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

    private function emptySlugConstraint($queryBuilder)
    {
        return $queryBuilder->expr()->or(
            $queryBuilder->expr()->eq(self::SLUG_FIELD, $queryBuilder->createNamedParameter('', ParameterType::STRING)),
            $queryBuilder->expr()->isNull(self::SLUG_FIELD),
            $queryBuilder->expr()->like(self::SLUG_FIELD, $queryBuilder->createNamedParameter(':dcValue%', ParameterType::STRING))
        );
    }

    private function slugColumnExists(): bool
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE);
        $schemaManager = $connection->createSchemaManager();

        return $schemaManager->tablesExist([self::TABLE])
            && $schemaManager->introspectTable(self::TABLE)->hasColumn(self::SLUG_FIELD);
    }
}
