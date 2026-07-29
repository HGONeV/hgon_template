<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('hgonTemplateDidYouKnowCategoryCleanup')]
final class HgonTemplateDidYouKnowCategoryCleanup implements UpgradeWizardInterface, RepeatableInterface
{
    private const TABLE = 'tx_hgontemplate_domain_model_didyouknow';
    private const RELATION_TABLE = 'sys_category_record_mm';
    private const CATEGORY_COLUMNS = ['sys_category', 'categories'];

    public function getTitle(): string
    {
        return 'HGON Template: Kategorien von „Schon gewusst?“ entfernen';
    }

    public function getDescription(): string
    {
        return 'Entfernt die nicht mehr verwendeten Kategoriezuordnungen und Kategoriespalten '
            . 'der „Schon gewusst?“-Datensätze.';
    }

    public function executeUpdate(): bool
    {
        if ($this->tableExists(self::RELATION_TABLE)) {
            $queryBuilder = $this->getConnection(self::RELATION_TABLE)->createQueryBuilder();
            $queryBuilder
                ->delete(self::RELATION_TABLE)
                ->where(
                    $queryBuilder->expr()->eq(
                        'tablenames',
                        $queryBuilder->createNamedParameter(self::TABLE, ParameterType::STRING)
                    )
                )
                ->executeStatement();
        }

        foreach (self::CATEGORY_COLUMNS as $columnName) {
            if ($this->columnExists(self::TABLE, $columnName)) {
                $connection = $this->getConnection(self::TABLE);
                $connection->executeStatement(
                    'ALTER TABLE ' . $connection->quoteIdentifier(self::TABLE)
                    . ' DROP COLUMN ' . $connection->quoteIdentifier($columnName)
                );
            }
        }

        return true;
    }

    public function updateNecessary(): bool
    {
        if ($this->categoryRelationsExist()) {
            return true;
        }

        foreach (self::CATEGORY_COLUMNS as $columnName) {
            if ($this->columnExists(self::TABLE, $columnName)) {
                return true;
            }
        }

        return false;
    }

    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }

    private function categoryRelationsExist(): bool
    {
        if (!$this->tableExists(self::RELATION_TABLE)) {
            return false;
        }

        $queryBuilder = $this->getConnection(self::RELATION_TABLE)->createQueryBuilder();
        $count = $queryBuilder
            ->count('*')
            ->from(self::RELATION_TABLE)
            ->where(
                $queryBuilder->expr()->eq(
                    'tablenames',
                    $queryBuilder->createNamedParameter(self::TABLE, ParameterType::STRING)
                )
            )
            ->executeQuery()
            ->fetchOne();

        return (int)$count > 0;
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        if (!$this->tableExists($tableName)) {
            return false;
        }

        foreach ($this->getConnection($tableName)->createSchemaManager()->listTableColumns($tableName) as $column) {
            if ($column->getName() === $columnName) {
                return true;
            }
        }

        return false;
    }

    private function tableExists(string $tableName): bool
    {
        return $this->getConnection($tableName)->createSchemaManager()->tablesExist([$tableName]);
    }

    private function getConnection(string $tableName): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName);
    }
}
