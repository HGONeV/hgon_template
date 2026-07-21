<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use Doctrine\DBAL\ParameterType;
use RuntimeException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\ReferenceIndexUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Migrates the FAL field name used by the legacy TYPO3 8 news header image TCA.
 *
 * The database column was already named tx_hgontemplate_header_image, but the
 * old getFileFieldTCAConfig() call stored its sys_file_reference records with
 * fieldname "image". TCA type "file" now uses the actual database field name.
 */
#[UpgradeWizard('hgonTemplateNewsHeaderImageReferenceMigration')]
final class HgonTemplateNewsHeaderImageReferenceMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const FILE_REFERENCE_TABLE = 'sys_file_reference';
    private const NEWS_TABLE = 'tx_news_domain_model_news';
    private const LEGACY_FIELD_NAME = 'image';
    private const NEW_FIELD_NAME = 'tx_hgontemplate_header_image';

    public function getTitle(): string
    {
        return 'HGON Template: News-Headerbildreferenzen migrieren';
    }

    public function getDescription(): string
    {
        return 'Korrigiert den historischen FAL-Feldnamen der separaten News-Headerbilder, '
            . 'ohne bereits korrekt verknüpfte Headerbilder zu überschreiben.';
    }

    public function executeUpdate(): bool
    {
        $references = $this->findMigratableReferences();
        if ($references === []) {
            return true;
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(self::FILE_REFERENCE_TABLE);
        $referenceIndex = GeneralUtility::makeInstance(ReferenceIndex::class);

        $connection->transactional(function (Connection $connection) use ($references, $referenceIndex): void {
            foreach ($references as $reference) {
                $affectedReferences = $connection->update(
                    self::FILE_REFERENCE_TABLE,
                    ['fieldname' => self::NEW_FIELD_NAME],
                    [
                        'uid' => $reference['referenceUid'],
                        'tablenames' => self::NEWS_TABLE,
                        'fieldname' => self::LEGACY_FIELD_NAME,
                        'deleted' => 0,
                    ],
                    [
                        'fieldname' => ParameterType::STRING,
                        'uid' => ParameterType::INTEGER,
                        'tablenames' => ParameterType::STRING,
                        'deleted' => ParameterType::INTEGER,
                    ]
                );
                if ($affectedReferences !== 1) {
                    throw new RuntimeException(sprintf(
                        'Die News-Headerbildreferenz %d konnte nicht eindeutig migriert werden.',
                        $reference['referenceUid']
                    ));
                }

                $connection->update(
                    self::NEWS_TABLE,
                    [self::NEW_FIELD_NAME => 1],
                    ['uid' => $reference['newsUid']],
                    [self::NEW_FIELD_NAME => ParameterType::INTEGER, 'uid' => ParameterType::INTEGER]
                );

                $referenceIndex->updateRefIndexTable(
                    self::NEWS_TABLE,
                    $reference['newsUid'],
                    false,
                    $reference['newsWorkspaceUid']
                );
                $referenceIndex->updateRefIndexTable(
                    self::FILE_REFERENCE_TABLE,
                    $reference['referenceUid'],
                    false,
                    $reference['referenceWorkspaceUid']
                );
            }
        });

        return true;
    }

    public function updateNecessary(): bool
    {
        return $this->findMigratableReferences() !== [];
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
            ReferenceIndexUpdatedPrerequisite::class,
        ];
    }

    /**
     * Selects at most one legacy reference per news record. A current header
     * reference always wins, so manually corrected records remain untouched.
     *
     * @return list<array{referenceUid: int, newsUid: int, newsWorkspaceUid: int, referenceWorkspaceUid: int}>
     */
    private function findMigratableReferences(): array
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable(self::FILE_REFERENCE_TABLE);

        $currentNewsUids = $queryBuilder
            ->select('active_reference.uid_foreign')
            ->from(self::FILE_REFERENCE_TABLE, 'active_reference')
            ->where(
                $queryBuilder->expr()->eq(
                    'active_reference.tablenames',
                    $queryBuilder->createNamedParameter(self::NEWS_TABLE)
                ),
                $queryBuilder->expr()->eq(
                    'active_reference.fieldname',
                    $queryBuilder->createNamedParameter(self::NEW_FIELD_NAME)
                ),
                $queryBuilder->expr()->eq('active_reference.deleted', 0)
            )
            ->executeQuery()
            ->fetchFirstColumn();
        $newsWithCurrentHeader = array_fill_keys(array_map('intval', $currentNewsUids), true);

        $queryBuilder = $connectionPool->getQueryBuilderForTable(self::FILE_REFERENCE_TABLE);
        $legacyReferences = $queryBuilder
            ->select(
                'legacy.uid',
                'legacy.uid_foreign',
                'legacy.t3ver_wsid AS reference_workspace_uid',
                'news.t3ver_wsid AS news_workspace_uid'
            )
            ->from(self::FILE_REFERENCE_TABLE, 'legacy')
            ->innerJoin(
                'legacy',
                self::NEWS_TABLE,
                'news',
                $queryBuilder->expr()->eq('news.uid', 'legacy.uid_foreign')
            )
            ->where(
                $queryBuilder->expr()->eq(
                    'legacy.tablenames',
                    $queryBuilder->createNamedParameter(self::NEWS_TABLE)
                ),
                $queryBuilder->expr()->eq(
                    'legacy.fieldname',
                    $queryBuilder->createNamedParameter(self::LEGACY_FIELD_NAME)
                ),
                $queryBuilder->expr()->eq('legacy.deleted', 0),
                $queryBuilder->expr()->eq('news.deleted', 0)
            )
            ->orderBy('legacy.uid_foreign')
            ->addOrderBy('legacy.sorting_foreign')
            ->addOrderBy('legacy.uid')
            ->executeQuery()
            ->fetchAllAssociative();

        $result = [];
        foreach ($legacyReferences as $legacyReference) {
            $newsUid = (int)$legacyReference['uid_foreign'];
            if ($newsUid <= 0 || isset($newsWithCurrentHeader[$newsUid])) {
                continue;
            }

            $result[] = [
                'referenceUid' => (int)$legacyReference['uid'],
                'newsUid' => $newsUid,
                'newsWorkspaceUid' => (int)$legacyReference['news_workspace_uid'],
                'referenceWorkspaceUid' => (int)$legacyReference['reference_workspace_uid'],
            ];
            $newsWithCurrentHeader[$newsUid] = true;
        }

        return $result;
    }
}
