<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use Doctrine\DBAL\ArrayParameterType;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Schema\Exception\DefaultTcaSchemaTablePositionException;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('hgonTemplateSchemaAndContentMigration')]
final class HgonTemplateSchemaAndContentMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const EXT_KEY = 'hgon_template';
    private const OLD_PAGE_ARTICLE_IMAGE = 'tx_rkwbasics_article_image';
    private const OLD_PAGE_TEASER_TEXT = 'tx_rkwbasics_teaser_text';
    private const OLD_PAGE_ARTICLE_IMAGE_REFERENCE_FIELD_NAMES = [
        self::OLD_PAGE_ARTICLE_IMAGE,
        'txRkwBasicsArticleImage',
    ];
    private const NEW_PAGE_ARTICLE_IMAGE = 'tx_hgontemplate_article_image';
    private const NEW_PAGE_TEASER_TEXT = 'tx_hgontemplate_teaser_text';

    public function getTitle(): string
    {
        return 'HGON Template: Schema- und CType-Migration';
    }

    public function getDescription(): string
    {
        return 'Aktualisiert das Extension-Schema aus ext_tables.sql und migriert tt_content von list_type auf CType.';
    }

    public function executeUpdate(): bool
    {
        $statements = $this->getCreateTableStatements();
        if ($statements !== []) {
            $schemaMigrator = GeneralUtility::makeInstance(SchemaMigrator::class);
            try {
                $errors = $schemaMigrator->install($statements);
                if ($errors !== []) {
                    error_log(sprintf('[%s] Schema migration errors: %s', self::EXT_KEY, implode('; ', $errors)));
                }
            } catch (DefaultTcaSchemaTablePositionException $exception) {
                // Missing foreign table TCA (e.g. optional extensions) should not block list_type migration.
                error_log(sprintf(
                    '[%s] Schema migration skipped due to missing TCA table: %s',
                    self::EXT_KEY,
                    $exception->getMessage()
                ));
            }
        }

        $this->migrateRkwBasicsPageFields();
        $this->dropLegacyRkwBasicsPageColumns();
        $this->migrateListTypeToCType();
        return true;
    }

    public function updateNecessary(): bool
    {
        if ($this->hasMissingKnownColumns()) {
            return true;
        }

        if ($this->hasLegacyRkwBasicsPageColumns()) {
            return true;
        }

        if ($this->hasLegacyRkwBasicsFileReferences()) {
            return true;
        }

        if ($this->hasListTypeRows()) {
            return true;
        }

        return false;
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    private function getCreateTableStatements(): array
    {
        $sqlFile = ExtensionManagementUtility::extPath(self::EXT_KEY, 'ext_tables.sql');
        if (!is_file($sqlFile)) {
            return [];
        }

        $sqlReader = GeneralUtility::makeInstance(SqlReader::class);
        return $sqlReader->getCreateTableStatementArray((string)file_get_contents($sqlFile));
    }

    private function migrateListTypeToCType(): void
    {
        $signatures = $this->getPluginSignatures();
        if ($signatures === []) {
            return;
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        foreach ($signatures as $signature) {
            $connection->executeStatement(
                'UPDATE tt_content SET CType = :ctype, list_type = :listType WHERE CType = :oldCType AND list_type = :signature',
                [
                    'ctype' => $signature,
                    'listType' => '',
                    'oldCType' => 'list',
                    'signature' => $signature,
                ]
            );
        }
    }

    private function hasListTypeRows(): bool
    {
        $signatures = $this->getPluginSignatures();
        if ($signatures === []) {
            return false;
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');

        $count = $queryBuilder
            ->count('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('list')),
                $queryBuilder->expr()->in('list_type', $queryBuilder->createNamedParameter($signatures, ArrayParameterType::STRING))
            )
            ->executeQuery()
            ->fetchOne();

        return (int)$count > 0;
    }

    private function hasMissingKnownColumns(): bool
    {
        $requiredColumns = [
            'pages' => [
                'tx_hgontemplate_contactperson',
                self::NEW_PAGE_ARTICLE_IMAGE,
                self::NEW_PAGE_TEASER_TEXT,
            ],
            'tx_mdnewsauthor_domain_model_newsauthor' => [
                'tx_hgontemplate_short_description',
                'tx_hgontemplate_longer_description',
                'phone2',
            ],
            'tx_sfeventmgt_domain_model_event' => [
                'tx_hgontemplate_event_type',
                'tx_hgontemplate_eventculinary',
            ],
        ];

        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        foreach ($requiredColumns as $table => $columns) {
            $connection = $connectionPool->getConnectionForTable($table);
            if (!in_array($table, $connection->createSchemaManager()->listTableNames(), true)) {
                continue;
            }

            $existingColumns = [];
            foreach ($connection->createSchemaManager()->listTableColumns($table) as $column) {
                $existingColumns[$column->getName()] = true;
            }

            foreach ($columns as $column) {
                if (!isset($existingColumns[$column])) {
                    return true;
                }
            }
        }

        return false;
    }

    private function migrateRkwBasicsPageFields(): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');

        if (
            $this->columnExists('pages', self::OLD_PAGE_ARTICLE_IMAGE)
            && $this->columnExists('pages', self::NEW_PAGE_ARTICLE_IMAGE)
        ) {
            $connection->executeStatement(
                sprintf(
                    'UPDATE pages SET %s = %s WHERE (%s = 0 OR %s IS NULL) AND %s > 0',
                    self::NEW_PAGE_ARTICLE_IMAGE,
                    self::OLD_PAGE_ARTICLE_IMAGE,
                    self::NEW_PAGE_ARTICLE_IMAGE,
                    self::NEW_PAGE_ARTICLE_IMAGE,
                    self::OLD_PAGE_ARTICLE_IMAGE
                )
            );
        }

        if (
            $this->columnExists('pages', self::OLD_PAGE_TEASER_TEXT)
            && $this->columnExists('pages', self::NEW_PAGE_TEASER_TEXT)
        ) {
            $connection->executeStatement(
                sprintf(
                    'UPDATE pages SET %s = %s WHERE (%s IS NULL OR %s = \'\') AND %s IS NOT NULL AND %s <> \'\'',
                    self::NEW_PAGE_TEASER_TEXT,
                    self::OLD_PAGE_TEASER_TEXT,
                    self::NEW_PAGE_TEASER_TEXT,
                    self::NEW_PAGE_TEASER_TEXT,
                    self::OLD_PAGE_TEASER_TEXT,
                    self::OLD_PAGE_TEASER_TEXT
                )
            );
        }

        if ($this->tableExists('sys_file_reference')) {
            $fileReferenceConnection = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('sys_file_reference');

            foreach (self::OLD_PAGE_ARTICLE_IMAGE_REFERENCE_FIELD_NAMES as $oldFieldName) {
                $fileReferenceConnection->update(
                    'sys_file_reference',
                    ['fieldname' => self::NEW_PAGE_ARTICLE_IMAGE],
                    [
                        'tablenames' => 'pages',
                        'fieldname' => $oldFieldName,
                    ]
                );
            }
        }
    }

    private function dropLegacyRkwBasicsPageColumns(): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');

        foreach ([self::OLD_PAGE_ARTICLE_IMAGE, self::OLD_PAGE_TEASER_TEXT] as $column) {
            if ($this->columnExists('pages', $column)) {
                $connection->executeStatement('ALTER TABLE pages DROP COLUMN ' . $column);
            }
        }
    }

    private function hasLegacyRkwBasicsPageColumns(): bool
    {
        return $this->columnExists('pages', self::OLD_PAGE_ARTICLE_IMAGE)
            || $this->columnExists('pages', self::OLD_PAGE_TEASER_TEXT);
    }

    private function hasLegacyRkwBasicsFileReferences(): bool
    {
        if (!$this->tableExists('sys_file_reference')) {
            return false;
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_file_reference');

        $count = $queryBuilder
            ->count('uid')
            ->from('sys_file_reference')
            ->where(
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter('pages')),
                $queryBuilder->expr()->in(
                    'fieldname',
                    $queryBuilder->createNamedParameter(self::OLD_PAGE_ARTICLE_IMAGE_REFERENCE_FIELD_NAMES, ArrayParameterType::STRING)
                )
            )
            ->executeQuery()
            ->fetchOne();

        return (int)$count > 0;
    }

    private function columnExists(string $table, string $column): bool
    {
        if (!$this->tableExists($table)) {
            return false;
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($table);

        foreach ($connection->createSchemaManager()->listTableColumns($table) as $existingColumn) {
            if ($existingColumn->getName() === $column) {
                return true;
            }
        }

        return false;
    }

    private function tableExists(string $table): bool
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($table);

        return in_array($table, $connection->createSchemaManager()->listTableNames(), true);
    }

    private function getPluginSignatures(): array
    {
        $extensionName = str_replace(' ', '', ucwords(str_replace('_', ' ', self::EXT_KEY)));
        $pluginNames = [
            'PageHighlight',
            'RandomAuthor',
            'SidebarContactPerson',
            'SiblingPagesOverview',
            'ChildrenPagesOverview',
            'PageSlider',
            'DonationForm',
            'SixReasons',
            'DidYouKnow',
            'Maps',
            'AuthorList',
            'ShowRelatedSidebar',
            'JournalHighlight',
            'Journal',
            'Header',
            'Sidebar',
        ];

        return array_map(
            static fn (string $pluginName): string => strtolower($extensionName . '_' . $pluginName),
            $pluginNames
        );
    }
}
