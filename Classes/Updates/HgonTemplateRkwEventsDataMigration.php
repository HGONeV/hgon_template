<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('hgonTemplateRkwEventsDataMigration')]
final class HgonTemplateRkwEventsDataMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const SOURCE_TABLE = 'tx_rkwevents_domain_model_event';
    private const TARGET_TABLE = 'tx_sfeventmgt_domain_model_event';
    private const SOURCE_PLACE_TABLE = 'tx_rkwevents_domain_model_eventplace';
    private const TARGET_LOCATION_TABLE = 'tx_sfeventmgt_domain_model_location';
    private const SOURCE_PID = 42;
    private const TARGET_PID = 37;

    public function getTitle(): string
    {
        return 'HGON Template: RKW Events Daten nach sf_event_mgt migrieren';
    }

    public function getDescription(): string
    {
        return 'Migriert konservativ die Kerndaten alter RKW-Events von PID 42 nach sf_event_mgt auf PID 37.';
    }

    public function executeUpdate(): bool
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $sourceConnection = $connectionPool->getConnectionForTable(self::SOURCE_TABLE);
        $targetConnection = $connectionPool->getConnectionForTable(self::TARGET_TABLE);
        $locationConnection = $connectionPool->getConnectionForTable(self::TARGET_LOCATION_TABLE);

        $sourceColumns = $this->getColumnNames($sourceConnection, self::SOURCE_TABLE);
        $targetColumns = $this->getColumnNames($targetConnection, self::TARGET_TABLE);
        $sourcePlaceColumns = $this->getColumnNames($sourceConnection, self::SOURCE_PLACE_TABLE);
        $targetLocationColumns = $this->getColumnNames($locationConnection, self::TARGET_LOCATION_TABLE);
        $sourceRows = $this->fetchSourceRows($sourceConnection);
        $uidMap = [];
        $locationUidMap = [];

        foreach ($sourceRows as $row) {
            $targetData = $this->buildTargetData(
                $row,
                $sourceColumns,
                $targetColumns,
                $sourceConnection,
                $sourcePlaceColumns,
                $locationConnection,
                $targetLocationColumns,
                $locationUidMap
            );
            $existingUid = $this->findExistingTargetUid($targetConnection, $row, $sourceColumns);

            if ($existingUid !== null) {
                $targetConnection->update(
                    self::TARGET_TABLE,
                    $targetData,
                    ['uid' => $existingUid],
                    ['uid' => ParameterType::INTEGER]
                );
                $uidMap[(int)$row['uid']] = $existingUid;
                continue;
            }

            $targetConnection->insert(self::TARGET_TABLE, $targetData);
            $uidMap[(int)$row['uid']] = (int)$targetConnection->lastInsertId(self::TARGET_TABLE);
        }

        $this->updateLocalizationParents($targetConnection, $sourceRows, $sourceColumns, $uidMap, $targetColumns);

        return true;
    }

    public function updateNecessary(): bool
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $sourceConnection = $connectionPool->getConnectionForTable(self::SOURCE_TABLE);
        $targetConnection = $connectionPool->getConnectionForTable(self::TARGET_TABLE);
        $sourceColumns = $this->getColumnNames($sourceConnection, self::SOURCE_TABLE);

        foreach ($this->fetchSourceRows($sourceConnection) as $row) {
            if ($this->findExistingTargetUid($targetConnection, $row, $sourceColumns) === null) {
                return true;
            }
        }

        return false;
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchSourceRows(Connection $connection): array
    {
        return $connection->createQueryBuilder()
            ->select('*')
            ->from(self::SOURCE_TABLE)
            ->where(
                'deleted = 0',
                'pid = :pid'
            )
            ->setParameter('pid', self::SOURCE_PID, ParameterType::INTEGER)
            ->orderBy('uid', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    private function buildTargetData(
        array $sourceRow,
        array $sourceColumns,
        array $targetColumns,
        Connection $sourceConnection,
        array $sourcePlaceColumns,
        Connection $locationConnection,
        array $targetLocationColumns,
        array &$locationUidMap
    ): array
    {
        $data = [
            'pid' => self::TARGET_PID,
            'title' => (string)($sourceRow['title'] ?? ''),
            'tstamp' => (int)($sourceRow['tstamp'] ?? time()),
            'crdate' => (int)($sourceRow['crdate'] ?? time()),
            'deleted' => 0,
        ];

        $this->copyIfTargetColumnExists($data, $targetColumns, 'cruser_id', (int)($sourceRow['cruser_id'] ?? 0));
        $this->copyIfTargetColumnExists($data, $targetColumns, 'hidden', (int)($sourceRow['hidden'] ?? 0));
        $this->copyIfTargetColumnExists($data, $targetColumns, 'starttime', (int)($sourceRow['starttime'] ?? 0));
        $this->copyIfTargetColumnExists($data, $targetColumns, 'endtime', (int)($sourceRow['endtime'] ?? 0));
        $this->copyIfTargetColumnExists($data, $targetColumns, 'sorting', (int)($sourceRow['sorting'] ?? 0));
        $this->copyIfTargetColumnExists($data, $targetColumns, 'fe_group', (string)($sourceRow['fe_group'] ?? ''));
        $this->copyIfTargetColumnExists($data, $targetColumns, 'sys_language_uid', (int)($sourceRow['sys_language_uid'] ?? 0));
        $this->copyIfTargetColumnExists($data, $targetColumns, 'l10n_parent', 0);
        $this->copyIfTargetColumnExists($data, $targetColumns, 'l10n_source', 0);

        if (isset($sourceRow['description'])) {
            $data['description'] = (string)$sourceRow['description'];
        }

        $subtitle = $this->firstAvailableValue($sourceRow, ['subtitle', 'sub_title', 'subTitle']);
        if ($subtitle !== null && isset($targetColumns['teaser'])) {
            $data['teaser'] = (string)$subtitle;
        }

        $startDate = $this->firstAvailableValue($sourceRow, ['startdate', 'start']);
        if ($startDate !== null) {
            $data['startdate'] = (int)$startDate;
        }

        $endDate = $this->firstAvailableValue($sourceRow, ['enddate', 'end']);
        if ($endDate !== null) {
            $data['enddate'] = (int)$endDate;
        }

        $price = $this->firstAvailableValue($sourceRow, ['price', 'costsReg', 'costs_reg']);
        if ($price !== null && isset($targetColumns['price'])) {
            $data['price'] = (float)$price;
            $this->copyIfTargetColumnExists($data, $targetColumns, 'currency', 'EUR');
        }

        $enableRegistration = $this->firstAvailableValue($sourceRow, ['enable_registration', 'regRequired', 'reg_required']);
        if ($enableRegistration !== null && isset($targetColumns['enable_registration'])) {
            $data['enable_registration'] = (int)$enableRegistration;
        }

        $link = $this->firstAvailableValue($sourceRow, ['link']);
        if ($link !== null && isset($targetColumns['link'])) {
            $data['link'] = (string)$link;
        }

        $placeUid = $this->firstAvailableValue($sourceRow, ['place', 'eventplace', 'event_place']);
        if (
            $placeUid !== null
            && isset($targetColumns['location'])
            && isset($sourcePlaceColumns['uid'])
        ) {
            $locationUid = $this->migrateLocation(
                (int)$placeUid,
                $sourceConnection,
                $sourcePlaceColumns,
                $locationConnection,
                $targetLocationColumns,
                $locationUidMap
            );
            if ($locationUid !== null) {
                $data['location'] = $locationUid;
            }
        }

        if (
            isset($targetColumns['organisator'])
            && isset($sourceColumns['contactPerson'])
            && empty($sourceRow['contactPerson'])
        ) {
            $data['organisator'] = 0;
        }

        return $data;
    }

    private function migrateLocation(
        int $sourcePlaceUid,
        Connection $sourceConnection,
        array $sourcePlaceColumns,
        Connection $locationConnection,
        array $targetLocationColumns,
        array &$locationUidMap
    ): ?int {
        if ($sourcePlaceUid <= 0) {
            return null;
        }

        if (isset($locationUidMap[$sourcePlaceUid])) {
            return $locationUidMap[$sourcePlaceUid];
        }

        $placeRow = $sourceConnection->createQueryBuilder()
            ->select('*')
            ->from(self::SOURCE_PLACE_TABLE)
            ->where(
                'deleted = 0',
                'uid = :uid'
            )
            ->setParameter('uid', $sourcePlaceUid, ParameterType::INTEGER)
            ->executeQuery()
            ->fetchAssociative();

        if (!is_array($placeRow)) {
            return null;
        }

        $title = (string)($this->firstAvailableValue($placeRow, ['title', 'name']) ?? '');
        $address = (string)($placeRow['address'] ?? '');
        $zip = (string)($placeRow['zip'] ?? '');
        $city = (string)($placeRow['city'] ?? '');

        $existingUid = $this->findExistingLocationUid($locationConnection, $title, $address, $zip, $city);
        if ($existingUid !== null) {
            $locationUidMap[$sourcePlaceUid] = $existingUid;
            return $existingUid;
        }

        $locationData = [
            'pid' => self::TARGET_PID,
            'title' => $title,
            'tstamp' => (int)($placeRow['tstamp'] ?? time()),
            'crdate' => (int)($placeRow['crdate'] ?? time()),
            'deleted' => 0,
        ];

        $this->copyIfTargetColumnExists($locationData, $targetLocationColumns, 'hidden', (int)($placeRow['hidden'] ?? 0));
        $this->copyIfTargetColumnExists($locationData, $targetLocationColumns, 'sys_language_uid', (int)($placeRow['sys_language_uid'] ?? 0));
        $this->copyIfTargetColumnExists($locationData, $targetLocationColumns, 'l10n_parent', 0);
        $this->copyIfTargetColumnExists($locationData, $targetLocationColumns, 'l10n_source', 0);
        $this->copyIfTargetColumnExists($locationData, $targetLocationColumns, 'address', $address);
        $this->copyIfTargetColumnExists($locationData, $targetLocationColumns, 'zip', $zip);
        $this->copyIfTargetColumnExists($locationData, $targetLocationColumns, 'city', $city);
        $this->copyIfTargetColumnExists(
            $locationData,
            $targetLocationColumns,
            'country',
            (string)($this->firstAvailableValue($placeRow, ['country', 'country_short_name', 'country_iso']) ?? '')
        );
        $this->copyIfTargetColumnExists(
            $locationData,
            $targetLocationColumns,
            'description',
            (string)($placeRow['description'] ?? '')
        );
        $this->copyIfTargetColumnExists(
            $locationData,
            $targetLocationColumns,
            'link',
            (string)($placeRow['link'] ?? '')
        );
        $this->copyIfTargetColumnExists(
            $locationData,
            $targetLocationColumns,
            'longitude',
            (float)($this->firstAvailableValue($placeRow, ['longitude', 'lng']) ?? 0.0)
        );
        $this->copyIfTargetColumnExists(
            $locationData,
            $targetLocationColumns,
            'latitude',
            (float)($this->firstAvailableValue($placeRow, ['latitude', 'lat']) ?? 0.0)
        );

        $locationConnection->insert(self::TARGET_LOCATION_TABLE, $locationData);
        $newUid = (int)$locationConnection->lastInsertId(self::TARGET_LOCATION_TABLE);
        $locationUidMap[$sourcePlaceUid] = $newUid;

        return $newUid;
    }

    private function findExistingLocationUid(
        Connection $connection,
        string $title,
        string $address,
        string $zip,
        string $city
    ): ?int {
        if ($title === '' && $address === '' && $zip === '' && $city === '') {
            return null;
        }

        $uid = $connection->createQueryBuilder()
            ->select('uid')
            ->from(self::TARGET_LOCATION_TABLE)
            ->where(
                'deleted = 0',
                'pid = :pid',
                'title = :title',
                'address = :address',
                'zip = :zip',
                'city = :city'
            )
            ->setParameter('pid', self::TARGET_PID, ParameterType::INTEGER)
            ->setParameter('title', $title)
            ->setParameter('address', $address)
            ->setParameter('zip', $zip)
            ->setParameter('city', $city)
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        return $uid === false ? null : (int)$uid;
    }

    /**
     * @param array<string, mixed> $sourceRow
     * @param array<string, bool> $sourceColumns
     */
    private function findExistingTargetUid(Connection $connection, array $sourceRow, array $sourceColumns): ?int
    {
        $startDate = $this->firstAvailableValue($sourceRow, ['startdate', 'start']);

        if ($startDate === null || empty($sourceRow['title'])) {
            return null;
        }

        $queryBuilder = $connection->createQueryBuilder();
        $uid = $queryBuilder
            ->select('uid')
            ->from(self::TARGET_TABLE)
            ->where(
                'deleted = 0',
                'pid = :pid',
                'title = :title',
                'startdate = :startdate',
                'sys_language_uid = :language'
            )
            ->setParameter('pid', self::TARGET_PID, ParameterType::INTEGER)
            ->setParameter('title', (string)$sourceRow['title'])
            ->setParameter('startdate', (int)$startDate, ParameterType::INTEGER)
            ->setParameter(
                'language',
                (int)((isset($sourceColumns['sys_language_uid']) ? ($sourceRow['sys_language_uid'] ?? 0) : 0)),
                ParameterType::INTEGER
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        return $uid === false ? null : (int)$uid;
    }

    /**
     * @param array<int, array<string, mixed>> $sourceRows
     * @param array<string, bool> $sourceColumns
     * @param array<int, int> $uidMap
     * @param array<string, bool> $targetColumns
     */
    private function updateLocalizationParents(
        Connection $connection,
        array $sourceRows,
        array $sourceColumns,
        array $uidMap,
        array $targetColumns
    ): void {
        if (!isset($sourceColumns['l10n_parent'], $targetColumns['l10n_parent'])) {
            return;
        }

        foreach ($sourceRows as $row) {
            $sourceUid = (int)$row['uid'];
            $sourceParentUid = (int)($row['l10n_parent'] ?? 0);

            if ($sourceParentUid <= 0 || !isset($uidMap[$sourceUid], $uidMap[$sourceParentUid])) {
                continue;
            }

            $connection->update(
                self::TARGET_TABLE,
                ['l10n_parent' => $uidMap[$sourceParentUid]],
                ['uid' => $uidMap[$sourceUid]],
                [
                    'l10n_parent' => ParameterType::INTEGER,
                    'uid' => ParameterType::INTEGER,
                ]
            );
        }
    }

    /**
     * @return array<string, bool>
     */
    private function getColumnNames(Connection $connection, string $table): array
    {
        $columns = [];
        foreach ($connection->createSchemaManager()->listTableColumns($table) as $column) {
            $columns[$column->getName()] = true;
        }

        return $columns;
    }

    /**
     * @param array<string, mixed> $row
     * @param string[] $candidates
     */
    private function firstAvailableValue(array $row, array $candidates): mixed
    {
        foreach ($candidates as $candidate) {
            if (array_key_exists($candidate, $row) && $row[$candidate] !== null && $row[$candidate] !== '') {
                return $row[$candidate];
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, bool> $targetColumns
     */
    private function copyIfTargetColumnExists(array &$data, array $targetColumns, string $column, mixed $value): void
    {
        if (isset($targetColumns[$column])) {
            $data[$column] = $value;
        }
    }
}
