<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('hgonTemplateLegacyNewsletterSchemaCleanup')]
final class HgonTemplateLegacyNewsletterSchemaCleanup implements UpgradeWizardInterface, RepeatableInterface
{
    private const TABLES = [
        'tx_rkwnewsletter_domain_model_newsletter_donation_mm',
        'tx_rkwnewsletter_domain_model_newsletter_event_mm',
        'tx_rkwnewsletter_domain_model_newsletter_article_mm',
        'tx_rkwnewsletter_domain_model_newsletter_news_mm',
        'tx_rkwnewsletter_domain_model_newsletter',
    ];

    public function getTitle(): string
    {
        return 'HGON Template: alte RKW Newsletter-Tabellen entfernen';
    }

    public function getDescription(): string
    {
        return 'Entfernt nicht mehr genutzte Tabellen der ehemaligen RKW Newsletter-Funktion.';
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
        return [];
    }

    private function tableExists(string $table): bool
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($table);

        return in_array($table, $connection->createSchemaManager()->listTableNames(), true);
    }
}
