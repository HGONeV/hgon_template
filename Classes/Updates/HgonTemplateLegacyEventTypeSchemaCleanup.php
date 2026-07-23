<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('hgonTemplateLegacyEventTypeSchemaCleanup')]
final class HgonTemplateLegacyEventTypeSchemaCleanup implements UpgradeWizardInterface, RepeatableInterface
{
    private const TABLE = 'tx_sfeventmgt_domain_model_event';
    private const COLUMN = 'tx_hgontemplate_event_type';

    public function getTitle(): string
    {
        return 'HGON Template: veralteten Veranstaltungs-Typ entfernen';
    }

    public function getDescription(): string
    {
        return 'Entfernt das nicht mehr verwendete Feld tx_hgontemplate_event_type. '
            . 'Die Einordnung als Arbeitskreistreffen erfolgt über die Veranstaltungskategorie.';
    }

    public function executeUpdate(): bool
    {
        if ($this->columnExists()) {
            $this->getConnection()
                ->createSchemaManager()
                ->dropColumn(self::TABLE, self::COLUMN);
        }

        return true;
    }

    public function updateNecessary(): bool
    {
        return $this->columnExists();
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
            HgonTemplateEventDocumentTypeCategoryMigration::class,
        ];
    }

    private function columnExists(): bool
    {
        $schemaManager = $this->getConnection()->createSchemaManager();
        if (!in_array(self::TABLE, $schemaManager->listTableNames(), true)) {
            return false;
        }

        foreach ($schemaManager->listTableColumns(self::TABLE) as $column) {
            if ($column->getName() === self::COLUMN) {
                return true;
            }
        }

        return false;
    }

    private function getConnection(): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(self::TABLE);
    }
}
