<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('hgonTemplateEventRegistrationLimitMigration')]
final class HgonTemplateEventRegistrationLimitMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const TABLE = 'tx_sfeventmgt_domain_model_event';
    private const FIELD = 'max_registrations_per_user';
    private const MINIMUM = 1;

    public function getTitle(): string
    {
        return 'HGON Template: Registrierungs-Limit bestehender Veranstaltungen korrigieren';
    }

    public function getDescription(): string
    {
        return 'Setzt das maximale Registrierungs-Limit aktiver Veranstaltungen mit einem ungültigen Wert '
            . 'kleiner als 1 auf 1. Bereits konfigurierte höhere Limits bleiben unverändert.';
    }

    public function executeUpdate(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE);

        $queryBuilder
            ->update(self::TABLE)
            ->set(self::FIELD, self::MINIMUM)
            ->where(
                $queryBuilder->expr()->eq(
                    'deleted',
                    $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
                ),
                $queryBuilder->expr()->lt(
                    self::FIELD,
                    $queryBuilder->createNamedParameter(self::MINIMUM, ParameterType::INTEGER)
                )
            )
            ->executeStatement();

        return true;
    }

    public function updateNecessary(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE);

        $count = $queryBuilder
            ->count('uid')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq(
                    'deleted',
                    $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
                ),
                $queryBuilder->expr()->lt(
                    self::FIELD,
                    $queryBuilder->createNamedParameter(self::MINIMUM, ParameterType::INTEGER)
                )
            )
            ->executeQuery()
            ->fetchOne();

        return (int)$count > 0;
    }

    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }
}
