<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use Doctrine\DBAL\ArrayParameterType;
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
 * Uses remaining legacy page teaser images as HGON page header fallbacks.
 *
 * Existing header image references always win.
 */
#[UpgradeWizard('hgonTemplatePageHeaderImageReferenceMigration')]
final class HgonTemplatePageHeaderImageReferenceMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const TABLE = 'sys_file_reference';
    private const PAGE_TABLE = 'pages';
    private const NEW_FIELD_NAME = 'tx_hgontemplate_article_image';
    private const LEGACY_FIELD_NAMES = [
        'tx_rkwbasics_teaser_image',
        'txRkwBasicsTeaserImage',
    ];

    public function getTitle(): string
    {
        return 'HGON Template: Verbliebene Seiten-Headerbilder migrieren';
    }

    public function getDescription(): string
    {
        return 'Übernimmt alte RKW-Basics-Teaserbilder als Seitenheader, '
            . 'sofern die Seite noch kein aktuelles Headerbild besitzt.';
    }

    public function executeUpdate(): bool
    {
        $references = $this->findMigratableReferences();
        if ($references === []) {
            return true;
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(self::TABLE);
        $referenceIndex = GeneralUtility::makeInstance(ReferenceIndex::class);

        $connection->transactional(function (Connection $connection) use ($references, $referenceIndex): void {
            foreach ($references as $reference) {
                $affectedReferences = $connection->update(
                    self::TABLE,
                    ['fieldname' => self::NEW_FIELD_NAME],
                    ['uid' => $reference['referenceUid']],
                    ['fieldname' => ParameterType::STRING, 'uid' => ParameterType::INTEGER]
                );
                if ($affectedReferences !== 1) {
                    throw new RuntimeException(sprintf(
                        'Die Legacy-Bildreferenz %d konnte nicht eindeutig migriert werden.',
                        $reference['referenceUid']
                    ));
                }

                $connection->update(
                    self::PAGE_TABLE,
                    [self::NEW_FIELD_NAME => 1],
                    ['uid' => $reference['pageUid']],
                    [self::NEW_FIELD_NAME => ParameterType::INTEGER, 'uid' => ParameterType::INTEGER]
                );

                $referenceIndex->updateRefIndexTable(
                    self::PAGE_TABLE,
                    $reference['pageUid'],
                    false,
                    $reference['pageWorkspaceUid']
                );
                $referenceIndex->updateRefIndexTable(
                    self::TABLE,
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
     * Selects at most one legacy reference per page. References on pages which
     * already have a current header image are deliberately left untouched.
     *
     * @return list<array{referenceUid: int, pageUid: int, pageWorkspaceUid: int, referenceWorkspaceUid: int}>
     */
    private function findMigratableReferences(): array
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable(self::TABLE);

        $currentPageUids = $queryBuilder
            ->select('active_reference.uid_foreign')
            ->from(self::TABLE, 'active_reference')
            ->where(
                $queryBuilder->expr()->eq('active_reference.tablenames', $queryBuilder->createNamedParameter(self::PAGE_TABLE)),
                $queryBuilder->expr()->eq('active_reference.fieldname', $queryBuilder->createNamedParameter(self::NEW_FIELD_NAME)),
                $queryBuilder->expr()->eq('active_reference.deleted', 0)
            )
            ->executeQuery()
            ->fetchFirstColumn();
        $pagesWithCurrentHeader = array_fill_keys(array_map('intval', $currentPageUids), true);

        $queryBuilder = $connectionPool->getQueryBuilderForTable(self::TABLE);
        $legacyReferences = $queryBuilder
            ->select(
                'legacy.uid',
                'legacy.uid_foreign',
                'legacy.t3ver_wsid AS reference_workspace_uid',
                'page.t3ver_wsid AS page_workspace_uid'
            )
            ->from(self::TABLE, 'legacy')
            ->innerJoin(
                'legacy',
                self::PAGE_TABLE,
                'page',
                $queryBuilder->expr()->eq('page.uid', 'legacy.uid_foreign')
            )
            ->where(
                $queryBuilder->expr()->eq('legacy.tablenames', $queryBuilder->createNamedParameter(self::PAGE_TABLE)),
                $queryBuilder->expr()->in(
                    'legacy.fieldname',
                    $queryBuilder->createNamedParameter(self::LEGACY_FIELD_NAMES, ArrayParameterType::STRING)
                ),
                $queryBuilder->expr()->eq('legacy.deleted', 0),
                $queryBuilder->expr()->eq('page.deleted', 0)
            )
            ->orderBy('legacy.uid_foreign')
            ->addOrderBy('legacy.sorting_foreign')
            ->addOrderBy('legacy.uid')
            ->executeQuery()
            ->fetchAllAssociative();

        $result = [];
        foreach ($legacyReferences as $legacyReference) {
            $pageUid = (int)$legacyReference['uid_foreign'];
            if ($pageUid <= 0 || isset($pagesWithCurrentHeader[$pageUid])) {
                continue;
            }

            $result[] = [
                'referenceUid' => (int)$legacyReference['uid'],
                'pageUid' => $pageUid,
                'pageWorkspaceUid' => (int)$legacyReference['page_workspace_uid'],
                'referenceWorkspaceUid' => (int)$legacyReference['reference_workspace_uid'],
            ];
            $pagesWithCurrentHeader[$pageUid] = true;
        }

        return $result;
    }
}
