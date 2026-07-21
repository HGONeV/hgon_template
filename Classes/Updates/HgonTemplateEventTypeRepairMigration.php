<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use Doctrine\DBAL\ParameterType;
use RuntimeException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Repairs event types produced by the initial RKW Events data migration.
 *
 * In the legacy data, tx_hgon_workgroup_wgevent contains a positive workgroup
 * UID for workgroup meetings and 0 for standard events. The initial migration
 * treated the string "0" as a non-empty workgroup assignment.
 */
#[UpgradeWizard('hgonTemplateEventTypeRepairMigration')]
final class HgonTemplateEventTypeRepairMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const TABLE = 'tx_sfeventmgt_domain_model_event';
    private const TARGET_PID = 37;
    private const EVENT_TYPE_FIELD = 'tx_hgontemplate_event_type';
    private const WORKGROUP_FIELD = 'tx_hgon_workgroup_wgevent';

    public function getTitle(): string
    {
        return 'HGON Template: Event-Typen der migrierten Veranstaltungen korrigieren';
    }

    public function getDescription(): string
    {
        return 'Kennzeichnet migrierte Veranstaltungen mit einer positiven alten Arbeitskreis-Zuordnung '
            . 'als Arbeitskreistreffen und alle übrigen als Standardveranstaltung.';
    }

    public function executeUpdate(): bool
    {
        $events = $this->findEventsToRepair();
        if ($events === []) {
            return true;
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(self::TABLE);

        $connection->transactional(function (Connection $connection) use ($events): void {
            foreach ($events as $event) {
                $affectedRows = $connection->update(
                    self::TABLE,
                    [self::EVENT_TYPE_FIELD => $event['expectedType']],
                    [
                        'uid' => $event['uid'],
                        self::EVENT_TYPE_FIELD => $event['currentType'],
                        'deleted' => 0,
                    ],
                    [
                        self::EVENT_TYPE_FIELD => ParameterType::STRING,
                        'uid' => ParameterType::INTEGER,
                        'deleted' => ParameterType::INTEGER,
                    ]
                );
                if ($affectedRows !== 1) {
                    throw new RuntimeException(sprintf(
                        'Der Event-Typ der Veranstaltung %d konnte nicht eindeutig korrigiert werden.',
                        $event['uid']
                    ));
                }
            }
        });

        return true;
    }

    public function updateNecessary(): bool
    {
        return $this->findEventsToRepair() !== [];
    }

    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }

    /**
     * @return list<array{uid: int, currentType: string, expectedType: string}>
     */
    private function findEventsToRepair(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE);
        $rows = $queryBuilder
            ->select('uid', self::EVENT_TYPE_FIELD, self::WORKGROUP_FIELD)
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter(self::TARGET_PID, ParameterType::INTEGER)
                ),
                $queryBuilder->expr()->eq(
                    'deleted',
                    $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
                )
            )
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();

        $result = [];
        foreach ($rows as $row) {
            $currentType = (string)$row[self::EVENT_TYPE_FIELD];
            $expectedType = $this->hasPositiveWorkgroupUid((string)($row[self::WORKGROUP_FIELD] ?? ''))
                ? 'workgroup'
                : 'standard';
            if ($currentType === $expectedType) {
                continue;
            }

            $result[] = [
                'uid' => (int)$row['uid'],
                'currentType' => $currentType,
                'expectedType' => $expectedType,
            ];
        }

        return $result;
    }

    private function hasPositiveWorkgroupUid(string $value): bool
    {
        foreach (preg_split('/\s*,\s*/', trim($value)) ?: [] as $item) {
            if ((int)$item > 0) {
                return true;
            }
        }

        return false;
    }
}
