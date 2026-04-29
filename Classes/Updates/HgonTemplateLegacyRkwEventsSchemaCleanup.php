<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('hgonTemplateLegacyRkwEventsSchemaCleanup')]
final class HgonTemplateLegacyRkwEventsSchemaCleanup implements UpgradeWizardInterface, RepeatableInterface
{
    private const TABLES = [
        'tx_rkwevents_domain_model_eventreservation',
        'tx_rkwevents_domain_model_eventplace',
        'tx_rkwevents_domain_model_event',
    ];

    public function getTitle(): string
    {
        return 'HGON Template: alte RKW Events-Tabellen entfernen';
    }

    public function getDescription(): string
    {
        return 'Entfernt die nicht mehr genutzten Tabellen der ehemaligen RKW Events-Funktion nach der Migration zu sf_event_mgt.';
    }

    public function executeUpdate(): bool
    {
        foreach (self::TABLES as $table) {
            if ($this->tableExists($table)) {
                GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getConnectionForTable($table)
                    ->executeStatement('DROP TABLE ' . $table);
            }
        }

        return true;
    }

    public function updateNecessary(): bool
    {
        foreach (self::TABLES as $table) {
            if ($this->tableExists($table)) {
                return true;
            }
        }

        return false;
    }

    public function getPrerequisites(): array
    {
        return [
            HgonTemplateRkwEventsDataMigration::class,
            HgonTemplateRkwEventsPluginMigration::class,
        ];
    }

    private function tableExists(string $table): bool
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($table);

        return in_array($table, $connection->createSchemaManager()->listTableNames(), true);
    }
}
