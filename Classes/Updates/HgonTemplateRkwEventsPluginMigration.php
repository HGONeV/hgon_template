<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('hgonTemplateRkwEventsPluginMigration')]
final class HgonTemplateRkwEventsPluginMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const LEGACY_LIST_TYPE = 'rkwevents_pi1';

    /**
     * Fixed target mapping agreed for this project.
     *
     * pid => CType
     */
    private const PID_TO_CTYPE = [
        3 => 'sfeventmgt_pieventlist',
        40 => 'sfeventmgt_pieventdetail',
        64 => 'sfeventmgt_pieventregistration',
    ];

    public function getTitle(): string
    {
        return 'HGON Template: RKW Events Plugins auf sf_event_mgt migrieren';
    }

    public function getDescription(): string
    {
        return 'Migriert alte rkwevents_pi1-Inhaltselemente anhand fixer Seiten-IDs auf die passenden sf_event_mgt-CType-Elemente.';
    }

    public function executeUpdate(): bool
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        foreach (self::PID_TO_CTYPE as $pid => $targetCType) {
            $connection->executeStatement(
                'UPDATE tt_content
                 SET CType = :targetCType,
                     list_type = :emptyListType,
                     pi_flexform = :emptyFlexform,
                     hidden = :visible,
                     tstamp = :tstamp
                 WHERE deleted = 0
                   AND pid = :pid
                   AND CType = :legacyCType
                   AND list_type = :legacyListType',
                [
                    'targetCType' => $targetCType,
                    'emptyListType' => '',
                    'emptyFlexform' => '',
                    'visible' => 0,
                    'tstamp' => time(),
                    'pid' => $pid,
                    'legacyCType' => 'list',
                    'legacyListType' => self::LEGACY_LIST_TYPE,
                ]
            );

            $connection->executeStatement(
                'UPDATE tt_content
                 SET hidden = :visible,
                     tstamp = :tstamp
                 WHERE deleted = 0
                   AND pid = :pid
                   AND CType = :targetCType',
                [
                    'visible' => 0,
                    'tstamp' => time(),
                    'pid' => $pid,
                    'targetCType' => $targetCType,
                ]
            );
        }

        return true;
    }

    public function updateNecessary(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');

        $count = $queryBuilder
            ->count('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('list')),
                $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter(self::LEGACY_LIST_TYPE)),
                $queryBuilder->expr()->in(
                    'pid',
                    $queryBuilder->createNamedParameter(array_keys(self::PID_TO_CTYPE), ArrayParameterType::INTEGER)
                )
            )
            ->executeQuery()
            ->fetchOne();

        return (int)$count > 0;
    }

    public function getPrerequisites(): array
    {
        return [];
    }
}
