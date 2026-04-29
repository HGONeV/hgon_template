<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('hgonTemplateRkwAuthorsToMdNewsAuthorMigration')]
final class HgonTemplateRkwAuthorsToMdNewsAuthorMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const SOURCE_TABLE = 'tx_rkwauthors_domain_model_authors';
    private const TARGET_TABLE = 'tx_mdnewsauthor_domain_model_newsauthor';

    public function getTitle(): string
    {
        return 'HGON Template: RKW-Autoren nach md_news_author migrieren';
    }

    public function getDescription(): string
    {
        return 'Migriert alte RKW-Autoren wiederholbar nach md_news_author und behaelt die UIDs fuer bestehende Referenzen bei.';
    }

    public function executeUpdate(): bool
    {
        $connection = $this->getConnection(self::TARGET_TABLE);
        if (!$this->tableExists($connection, self::SOURCE_TABLE) || !$this->tableExists($connection, self::TARGET_TABLE)) {
            return true;
        }

        $sourceColumns = $this->getColumnNames($connection, self::SOURCE_TABLE);
        $targetColumns = $this->getColumnNames($connection, self::TARGET_TABLE);
        $targetRows = $this->getRowsIndexedByUid(self::TARGET_TABLE);

        foreach ($this->getSourceRows() as $sourceRow) {
            $uid = (int)$sourceRow['uid'];
            $data = $this->buildTargetRow($sourceRow, $sourceColumns, $targetColumns);

            if (isset($targetRows[$uid])) {
                unset($data['uid']);
                $connection->update(self::TARGET_TABLE, $data, ['uid' => $uid]);
                continue;
            }

            $connection->insert(self::TARGET_TABLE, $data);
        }

        $this->migrateFileReferences();
        $this->updateImageCounters();
        $this->migrateNewsAuthorRelations();
        $this->updateNewsAuthorCounters();

        return true;
    }

    public function updateNecessary(): bool
    {
        $connection = $this->getConnection(self::TARGET_TABLE);
        if (!$this->tableExists($connection, self::SOURCE_TABLE) || !$this->tableExists($connection, self::TARGET_TABLE)) {
            return false;
        }

        $sourceColumns = $this->getColumnNames($connection, self::SOURCE_TABLE);
        $targetColumns = $this->getColumnNames($connection, self::TARGET_TABLE);
        $targetRows = $this->getRowsIndexedByUid(self::TARGET_TABLE);

        foreach ($this->getSourceRows() as $sourceRow) {
            $uid = (int)$sourceRow['uid'];
            if (!isset($targetRows[$uid])) {
                return true;
            }

            $expected = $this->buildTargetRow($sourceRow, $sourceColumns, $targetColumns);
            foreach ($expected as $column => $value) {
                if ($column === 'uid') {
                    continue;
                }
                if ((string)($targetRows[$uid][$column] ?? '') !== (string)$value) {
                    return true;
                }
            }
        }

        return $this->hasOldFileReferences() || $this->hasMissingNewsAuthorRelations();
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    private function getSourceRows(): array
    {
        $queryBuilder = $this->getConnection(self::SOURCE_TABLE)->createQueryBuilder();

        return $queryBuilder
            ->select('*')
            ->from(self::SOURCE_TABLE)
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    private function getRowsIndexedByUid(string $table): array
    {
        $queryBuilder = $this->getConnection($table)->createQueryBuilder();
        $rows = $queryBuilder
            ->select('*')
            ->from($table)
            ->executeQuery()
            ->fetchAllAssociative();

        $indexedRows = [];
        foreach ($rows as $row) {
            $indexedRows[(int)$row['uid']] = $row;
        }

        return $indexedRows;
    }

    private function buildTargetRow(array $sourceRow, array $sourceColumns, array $targetColumns): array
    {
        $uid = (int)$sourceRow['uid'];
        $firstName = (string)($this->firstAvailableValue($sourceRow, ['firstname', 'first_name', 'firstName', 'given_name']) ?? '');
        $lastName = (string)($this->firstAvailableValue($sourceRow, ['lastname', 'last_name', 'lastName', 'name']) ?? '');
        if ($lastName === '') {
            $lastName = 'Autor ' . $uid;
        }

        $longDescription = (string)($this->firstAvailableValue($sourceRow, [
            'tx_hgontemplate_longer_description',
            'function_description',
            'functionDescription',
            'description',
            'bio',
        ]) ?? '');
        $shortDescription = (string)($this->firstAvailableValue($sourceRow, [
            'tx_hgontemplate_short_description',
            'short_description',
            'function_description',
            'functionDescription',
        ]) ?? '');

        $data = [];
        $this->copyIfTargetColumnExists($data, $targetColumns, 'uid', $uid);
        foreach (['pid', 'tstamp', 'crdate', 'cruser_id', 'deleted', 'hidden', 'starttime', 'endtime', 'sorting', 'sys_language_uid', 'l10n_parent', 'l10n_diffsource'] as $column) {
            $this->copySourceColumnIfTargetColumnExists($data, $sourceRow, $sourceColumns, $targetColumns, $column);
        }

        $this->copyIfTargetColumnExists($data, $targetColumns, 'firstname', $firstName);
        $this->copyIfTargetColumnExists($data, $targetColumns, 'lastname', $lastName);
        $this->copyIfTargetColumnExists($data, $targetColumns, 'slug', $this->buildSlug($firstName, $lastName, $uid));
        $this->copyIfTargetColumnExists($data, $targetColumns, 'title', (string)($this->firstAvailableValue($sourceRow, ['title', 'academic_title']) ?? ''));
        $this->copyIfTargetColumnExists($data, $targetColumns, 'gender', (string)($this->firstAvailableValue($sourceRow, ['gender', 'sex']) ?? ''));
        $this->copyIfTargetColumnExists($data, $targetColumns, 'company', (string)($this->firstAvailableValue($sourceRow, ['company', 'organisation', 'organization']) ?? ''));
        $this->copyIfTargetColumnExists($data, $targetColumns, 'position', (string)($this->firstAvailableValue($sourceRow, ['position', 'function_title', 'functionTitle']) ?? ''));
        $this->copyIfTargetColumnExists($data, $targetColumns, 'phone', (string)($this->firstAvailableValue($sourceRow, ['phone', 'telephone']) ?? ''));
        $this->copyIfTargetColumnExists($data, $targetColumns, 'phone2', (string)($this->firstAvailableValue($sourceRow, ['phone2', 'phone_2', 'mobile']) ?? ''));
        $this->copyIfTargetColumnExists($data, $targetColumns, 'email', (string)($this->firstAvailableValue($sourceRow, ['email']) ?? ''));
        $this->copyIfTargetColumnExists($data, $targetColumns, 'www', (string)($this->firstAvailableValue($sourceRow, ['www', 'website', 'url']) ?? ''));
        foreach (['facebook', 'twitter', 'linkedin', 'xing'] as $column) {
            $this->copySourceColumnIfTargetColumnExists($data, $sourceRow, $sourceColumns, $targetColumns, $column);
        }
        $this->copyIfTargetColumnExists($data, $targetColumns, 'bio', $longDescription !== '' ? $longDescription : $shortDescription);
        $this->copyIfTargetColumnExists($data, $targetColumns, 'tx_hgontemplate_short_description', $shortDescription);
        $this->copyIfTargetColumnExists($data, $targetColumns, 'tx_hgontemplate_longer_description', $longDescription);
        $this->copyIfTargetColumnExists($data, $targetColumns, 'image', (int)($this->firstAvailableValue($sourceRow, ['image', 'image_boxes', 'imageBoxes']) ?? 0));

        return $data;
    }

    private function migrateFileReferences(): void
    {
        $connection = $this->getConnection('sys_file_reference');
        if (!$this->tableExists($connection, 'sys_file_reference')) {
            return;
        }

        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder
            ->update('sys_file_reference')
            ->set('tablenames', self::TARGET_TABLE)
            ->set('fieldname', 'image')
            ->where(
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter(self::SOURCE_TABLE)),
                $queryBuilder->expr()->in(
                    'fieldname',
                    $queryBuilder->createNamedParameter(['image_boxes', 'imageBoxes', 'image'], ArrayParameterType::STRING)
                )
            )
            ->executeStatement();
    }

    private function updateImageCounters(): void
    {
        $connection = $this->getConnection(self::TARGET_TABLE);
        $targetColumns = $this->getColumnNames($connection, self::TARGET_TABLE);
        if (!isset($targetColumns['image']) || !$this->tableExists($connection, 'sys_file_reference')) {
            return;
        }

        foreach ($this->getSourceRows() as $sourceRow) {
            $uid = (int)$sourceRow['uid'];
            $queryBuilder = $connection->createQueryBuilder();
            $constraints = [
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter(self::TARGET_TABLE)),
                $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter('image')),
                $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($uid, ParameterType::INTEGER)),
            ];
            $fileReferenceColumns = $this->getColumnNames($connection, 'sys_file_reference');
            if (isset($fileReferenceColumns['deleted'])) {
                $constraints[] = $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER));
            }

            $count = (int)$queryBuilder
                ->count('uid')
                ->from('sys_file_reference')
                ->where(...$constraints)
                ->executeQuery()
                ->fetchOne();

            if ($count > 0) {
                $connection->update(self::TARGET_TABLE, ['image' => $count], ['uid' => $uid]);
            }
        }
    }

    private function migrateNewsAuthorRelations(): void
    {
        $connection = $this->getConnection('tx_news_domain_model_news');
        if (
            !$this->tableExists($connection, 'tx_news_domain_model_news')
            || !$this->tableExists($connection, self::TARGET_TABLE)
            || !$this->tableExists($connection, 'tx_mdnewsauthor_news_newsauthor_mm')
            || !isset($this->getColumnNames($connection, 'tx_news_domain_model_news')['author'])
        ) {
            return;
        }

        $authorMap = $this->getAuthorNameMap();
        if ($authorMap === []) {
            return;
        }

        foreach ($this->getNewsRowsWithLegacyAuthor() as $newsRow) {
            $newsUid = (int)$newsRow['uid'];
            foreach ($this->resolveAuthorUids((string)$newsRow['author'], $authorMap) as $sorting => $authorUid) {
                if ($this->newsAuthorRelationExists($newsUid, $authorUid)) {
                    continue;
                }

                $connection->insert('tx_mdnewsauthor_news_newsauthor_mm', [
                    'uid_local' => $newsUid,
                    'uid_foreign' => $authorUid,
                    'sorting' => ($sorting + 1) * 256,
                    'sorting_foreign' => 0,
                ]);
            }
        }
    }

    private function updateNewsAuthorCounters(): void
    {
        $connection = $this->getConnection('tx_news_domain_model_news');
        if (
            !$this->tableExists($connection, 'tx_news_domain_model_news')
            || !$this->tableExists($connection, 'tx_mdnewsauthor_news_newsauthor_mm')
            || !isset($this->getColumnNames($connection, 'tx_news_domain_model_news')['news_author'])
        ) {
            return;
        }

        foreach ($this->getNewsRowsWithLegacyAuthor() as $newsRow) {
            $newsUid = (int)$newsRow['uid'];
            $queryBuilder = $connection->createQueryBuilder();
            $count = (int)$queryBuilder
                ->count('*')
                ->from('tx_mdnewsauthor_news_newsauthor_mm')
                ->where($queryBuilder->expr()->eq('uid_local', $queryBuilder->createNamedParameter($newsUid, ParameterType::INTEGER)))
                ->executeQuery()
                ->fetchOne();

            if ($count > 0) {
                $connection->update('tx_news_domain_model_news', ['news_author' => $count], ['uid' => $newsUid]);
            }
        }
    }

    private function hasOldFileReferences(): bool
    {
        $connection = $this->getConnection('sys_file_reference');
        if (!$this->tableExists($connection, 'sys_file_reference')) {
            return false;
        }

        $queryBuilder = $connection->createQueryBuilder();
        $count = $queryBuilder
            ->count('uid')
            ->from('sys_file_reference')
            ->where(
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter(self::SOURCE_TABLE)),
                $queryBuilder->expr()->in(
                    'fieldname',
                    $queryBuilder->createNamedParameter(['image_boxes', 'imageBoxes', 'image'], ArrayParameterType::STRING)
                )
            )
            ->executeQuery()
            ->fetchOne();

        return (int)$count > 0;
    }

    private function hasMissingNewsAuthorRelations(): bool
    {
        $connection = $this->getConnection('tx_news_domain_model_news');
        if (
            !$this->tableExists($connection, 'tx_news_domain_model_news')
            || !$this->tableExists($connection, self::TARGET_TABLE)
            || !$this->tableExists($connection, 'tx_mdnewsauthor_news_newsauthor_mm')
            || !isset($this->getColumnNames($connection, 'tx_news_domain_model_news')['author'])
        ) {
            return false;
        }

        $authorMap = $this->getAuthorNameMap();
        if ($authorMap === []) {
            return false;
        }

        foreach ($this->getNewsRowsWithLegacyAuthor() as $newsRow) {
            $newsUid = (int)$newsRow['uid'];
            foreach ($this->resolveAuthorUids((string)$newsRow['author'], $authorMap) as $authorUid) {
                if (!$this->newsAuthorRelationExists($newsUid, $authorUid)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getNewsRowsWithLegacyAuthor(): array
    {
        $queryBuilder = $this->getConnection('tx_news_domain_model_news')->createQueryBuilder();
        $constraints = [
            $queryBuilder->expr()->neq('author', $queryBuilder->createNamedParameter('')),
        ];
        if (isset($this->getColumnNames($this->getConnection('tx_news_domain_model_news'), 'tx_news_domain_model_news')['deleted'])) {
            $constraints[] = $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER));
        }

        return $queryBuilder
            ->select('uid', 'author')
            ->from('tx_news_domain_model_news')
            ->where(...$constraints)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    private function getAuthorNameMap(): array
    {
        $connection = $this->getConnection(self::TARGET_TABLE);
        if (!$this->tableExists($connection, self::TARGET_TABLE)) {
            return [];
        }

        $targetColumns = $this->getColumnNames($connection, self::TARGET_TABLE);
        if (!isset($targetColumns['firstname'], $targetColumns['lastname'])) {
            return [];
        }

        $queryBuilder = $connection->createQueryBuilder();
        $rows = $queryBuilder
            ->select('uid', 'firstname', 'lastname')
            ->from(self::TARGET_TABLE)
            ->executeQuery()
            ->fetchAllAssociative();

        $map = [];
        $ambiguousNames = [];
        foreach ($rows as $row) {
            $uid = (int)$row['uid'];
            $firstName = (string)($row['firstname'] ?? '');
            $lastName = (string)($row['lastname'] ?? '');
            foreach ([$firstName . ' ' . $lastName, $lastName . ' ' . $firstName, $lastName] as $name) {
                $normalized = $this->normalizeName($name);
                if ($normalized !== '') {
                    if (isset($map[$normalized]) && $map[$normalized] !== $uid) {
                        unset($map[$normalized]);
                        $ambiguousNames[$normalized] = true;
                        continue;
                    }
                    if (!isset($ambiguousNames[$normalized])) {
                        $map[$normalized] = $uid;
                    }
                }
            }
        }

        return $map;
    }

    private function resolveAuthorUids(string $legacyAuthor, array $authorMap): array
    {
        $uids = [];
        $parts = preg_split('/\s*(?:,|;|\/|\+|&|\bund\b)\s*/u', $legacyAuthor) ?: [];
        if (count($parts) === 1) {
            $parts = [$legacyAuthor];
        }

        foreach ($parts as $part) {
            $normalized = $this->normalizeName($part);
            if ($normalized !== '' && isset($authorMap[$normalized])) {
                $uids[] = $authorMap[$normalized];
            }
        }

        if ($uids === []) {
            $normalized = $this->normalizeName($legacyAuthor);
            if ($normalized !== '' && isset($authorMap[$normalized])) {
                $uids[] = $authorMap[$normalized];
            }
        }

        return array_values(array_unique($uids));
    }

    private function newsAuthorRelationExists(int $newsUid, int $authorUid): bool
    {
        $queryBuilder = $this->getConnection('tx_mdnewsauthor_news_newsauthor_mm')->createQueryBuilder();
        $count = $queryBuilder
            ->count('*')
            ->from('tx_mdnewsauthor_news_newsauthor_mm')
            ->where(
                $queryBuilder->expr()->eq('uid_local', $queryBuilder->createNamedParameter($newsUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($authorUid, ParameterType::INTEGER))
            )
            ->executeQuery()
            ->fetchOne();

        return (int)$count > 0;
    }

    private function buildSlug(string $firstName, string $lastName, int $uid): string
    {
        $slug = strtolower(trim($firstName . '-' . $lastName));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?: '';
        $slug = trim($slug, '-');

        return ($slug !== '' ? $slug : 'autor') . '-' . $uid;
    }

    private function normalizeName(string $name): string
    {
        $name = strip_tags($name);
        $name = html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $name = mb_strtolower($name, 'UTF-8');
        $name = preg_replace('/\s+/', ' ', $name) ?: '';

        return trim($name);
    }

    private function tableExists(Connection $connection, string $table): bool
    {
        return in_array($table, $connection->createSchemaManager()->listTableNames(), true);
    }

    private function getColumnNames(Connection $connection, string $table): array
    {
        $columns = [];
        foreach ($connection->createSchemaManager()->listTableColumns($table) as $column) {
            $columns[$column->getName()] = true;
        }

        return $columns;
    }

    private function firstAvailableValue(array $row, array $candidates): mixed
    {
        foreach ($candidates as $candidate) {
            if (array_key_exists($candidate, $row) && $row[$candidate] !== null && $row[$candidate] !== '') {
                return $row[$candidate];
            }
        }

        return null;
    }

    private function copyIfTargetColumnExists(array &$data, array $targetColumns, string $column, mixed $value): void
    {
        if (isset($targetColumns[$column])) {
            $data[$column] = $value;
        }
    }

    private function copySourceColumnIfTargetColumnExists(
        array &$data,
        array $sourceRow,
        array $sourceColumns,
        array $targetColumns,
        string $column
    ): void {
        if (isset($sourceColumns[$column], $targetColumns[$column])) {
            $data[$column] = $sourceRow[$column];
        }
    }

    private function getConnection(string $table): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
    }
}
