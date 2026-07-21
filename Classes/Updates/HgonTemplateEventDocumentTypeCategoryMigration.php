<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use Doctrine\DBAL\ParameterType;
use RuntimeException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('hgonTemplateEventDocumentTypeCategoryMigration')]
final class HgonTemplateEventDocumentTypeCategoryMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const SOURCE_TABLE = 'tx_rkwevents_domain_model_event';
    private const TARGET_TABLE = 'tx_sfeventmgt_domain_model_event';
    private const RELATION_TABLE = 'sys_category_record_mm';
    private const SOURCE_PID = 42;
    private const TARGET_PID = 37;
    private const CATEGORY_MAP = [
        2 => 105, // Arbeitskreistreffen
        3 => 106, // Exkursion
        4 => 108, // Fortbildung
        7 => 107, // Vortrag
        9 => 109, // Tagung
        10 => 110, // Erfassung
        11 => 111, // Arbeitseinsatz -> Sonstiges
    ];

    public function getTitle(): string
    {
        return 'HGON Template: alte Event-Dokumenttypen als Kategorien migrieren';
    }

    public function getDescription(): string
    {
        return 'Überträgt die alten RKW-Event-Dokumenttypen auf die neuen Veranstaltungskategorien. '
            . 'Bestehende Kategoriezuordnungen bleiben erhalten; Arbeitseinsätze werden als Sonstiges eingeordnet.';
    }

    public function executeUpdate(): bool
    {
        $this->assertRequiredCategoriesExist();
        $relations = $this->findMissingRelations();
        if ($relations === []) {
            return true;
        }

        $connection = $this->getConnection(self::RELATION_TABLE);
        $connection->transactional(function (Connection $connection) use ($relations): void {
            foreach ($relations as $relation) {
                if ($this->relationExists($connection, $relation['categoryUid'], $relation['eventUid'])) {
                    continue;
                }

                $connection->insert(
                    self::RELATION_TABLE,
                    [
                        'uid_local' => $relation['categoryUid'],
                        'uid_foreign' => $relation['eventUid'],
                        'tablenames' => self::TARGET_TABLE,
                        'fieldname' => 'category',
                        'sorting' => $this->nextCategorySorting($connection, $relation['categoryUid']),
                        'sorting_foreign' => $this->nextEventSorting($connection, $relation['eventUid']),
                    ],
                    [
                        'uid_local' => ParameterType::INTEGER,
                        'uid_foreign' => ParameterType::INTEGER,
                        'tablenames' => ParameterType::STRING,
                        'fieldname' => ParameterType::STRING,
                        'sorting' => ParameterType::INTEGER,
                        'sorting_foreign' => ParameterType::INTEGER,
                    ]
                );
            }
        });

        return true;
    }

    public function updateNecessary(): bool
    {
        if (!$this->requiredTablesExist()) {
            return false;
        }

        return $this->missingCategoryUids() !== [] || $this->findMissingRelations() !== [];
    }

    public function getPrerequisites(): array
    {
        return [HgonTemplateRkwEventsDataMigration::class];
    }

    /**
     * @return list<array{categoryUid: int, eventUid: int}>
     */
    private function findMissingRelations(): array
    {
        if (!$this->requiredTablesExist()) {
            return [];
        }

        $sourceConnection = $this->getConnection(self::SOURCE_TABLE);
        $targetConnection = $this->getConnection(self::TARGET_TABLE);
        $relationConnection = $this->getConnection(self::RELATION_TABLE);
        $sourceRows = $sourceConnection->createQueryBuilder()
            ->select('uid', 'title', 'start', 'sys_language_uid', 'document_type')
            ->from(self::SOURCE_TABLE)
            ->where(
                'deleted = 0',
                'pid = :pid',
                'document_type IN (:documentTypes)'
            )
            ->setParameter('pid', self::SOURCE_PID, ParameterType::INTEGER)
            ->setParameter('documentTypes', array_keys(self::CATEGORY_MAP), Connection::PARAM_INT_ARRAY)
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();

        $relations = [];
        foreach ($sourceRows as $sourceRow) {
            $categoryUid = self::CATEGORY_MAP[(int)$sourceRow['document_type']] ?? 0;
            if ($categoryUid <= 0) {
                continue;
            }

            $eventUids = $this->findTargetEventUids($targetConnection, $sourceRow);

            foreach ($eventUids as $eventUid) {
                $eventUid = (int)$eventUid;
                $key = $categoryUid . ':' . $eventUid;
                if (!$this->relationExists($relationConnection, $categoryUid, $eventUid)) {
                    $relations[$key] = ['categoryUid' => $categoryUid, 'eventUid' => $eventUid];
                }
            }
        }

        return array_values($relations);
    }

    /**
     * @param array<string, mixed> $sourceRow
     * @return list<int|string>
     */
    private function findTargetEventUids(Connection $connection, array $sourceRow): array
    {
        $queryBuilder = $connection->createQueryBuilder();
        $eventUids = $queryBuilder
            ->select('uid')
            ->from(self::TARGET_TABLE)
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter(self::TARGET_PID, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter((string)$sourceRow['title'])),
                $queryBuilder->expr()->eq('startdate', $queryBuilder->createNamedParameter((int)$sourceRow['start'], ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter((int)$sourceRow['sys_language_uid'], ParameterType::INTEGER))
            )
            ->executeQuery()
            ->fetchFirstColumn();

        if ($eventUids !== []) {
            return $eventUids;
        }

        $queryBuilder = $connection->createQueryBuilder();

        return $queryBuilder
            ->select('uid')
            ->from(self::TARGET_TABLE)
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter(self::TARGET_PID, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter((string)$sourceRow['title'])),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter((int)$sourceRow['sys_language_uid'], ParameterType::INTEGER)),
                $queryBuilder->expr()->like(
                    'slug',
                    $queryBuilder->createNamedParameter('%-' . (int)$sourceRow['uid'])
                )
            )
            ->executeQuery()
            ->fetchFirstColumn();
    }

    private function relationExists(Connection $connection, int $categoryUid, int $eventUid): bool
    {
        $queryBuilder = $connection->createQueryBuilder();
        $count = $queryBuilder
            ->count('*')
            ->from(self::RELATION_TABLE)
            ->where(
                $queryBuilder->expr()->eq('uid_local', $queryBuilder->createNamedParameter($categoryUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($eventUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter(self::TARGET_TABLE)),
                $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter('category'))
            )
            ->executeQuery()
            ->fetchOne();

        return (int)$count > 0;
    }

    private function nextCategorySorting(Connection $connection, int $categoryUid): int
    {
        $queryBuilder = $connection->createQueryBuilder();

        return (int)$queryBuilder
            ->count('*')
            ->from(self::RELATION_TABLE)
            ->where(
                $queryBuilder->expr()->eq('uid_local', $queryBuilder->createNamedParameter($categoryUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter(self::TARGET_TABLE)),
                $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter('category'))
            )
            ->executeQuery()
            ->fetchOne();
    }

    private function nextEventSorting(Connection $connection, int $eventUid): int
    {
        $queryBuilder = $connection->createQueryBuilder();

        return (int)$queryBuilder
            ->count('*')
            ->from(self::RELATION_TABLE)
            ->where(
                $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($eventUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter(self::TARGET_TABLE)),
                $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter('category'))
            )
            ->executeQuery()
            ->fetchOne() + 1;
    }

    private function assertRequiredCategoriesExist(): void
    {
        $missingCategoryUids = $this->missingCategoryUids();
        if ($missingCategoryUids !== []) {
            throw new RuntimeException(sprintf(
                'Die benötigten Veranstaltungskategorien fehlen: %s',
                implode(', ', $missingCategoryUids)
            ));
        }
    }

    /** @return list<int> */
    private function missingCategoryUids(): array
    {
        if (!$this->tableExists('sys_category')) {
            return array_values(self::CATEGORY_MAP);
        }

        $connection = $this->getConnection('sys_category');
        $queryBuilder = $connection->createQueryBuilder();
        $existingUids = array_map('intval', $queryBuilder
            ->select('uid')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->in('uid', $queryBuilder->createNamedParameter(array_values(self::CATEGORY_MAP), Connection::PARAM_INT_ARRAY)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->executeQuery()
            ->fetchFirstColumn());

        return array_values(array_diff(array_values(self::CATEGORY_MAP), $existingUids));
    }

    private function requiredTablesExist(): bool
    {
        return $this->tableExists(self::SOURCE_TABLE)
            && $this->tableExists(self::TARGET_TABLE)
            && $this->tableExists(self::RELATION_TABLE);
    }

    private function tableExists(string $table): bool
    {
        return in_array($table, $this->getConnection($table)->createSchemaManager()->listTableNames(), true);
    }

    private function getConnection(string $table): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
    }
}
