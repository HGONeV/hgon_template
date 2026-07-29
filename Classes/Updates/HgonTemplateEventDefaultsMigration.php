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

#[UpgradeWizard('hgonTemplateEventDefaultsMigration')]
final class HgonTemplateEventDefaultsMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const TABLE = 'tx_sfeventmgt_domain_model_event';
    private const DEFAULTS = [
        'max_registrations_per_user' => 1,
        'notify_admin' => 1,
    ];
    private const REGISTRATION_MODE_FIELD = 'tx_hgontemplate_registration_mode';
    private const REGISTRATION_MODE_DEFAULT = 'native';

    public function getTitle(): string
    {
        return 'HGON Template: Standardwerte bestehender Veranstaltungen korrigieren';
    }

    public function getDescription(): string
    {
        return 'Setzt ungültige Registrierungslimits auf 1 und aktiviert die Admin-Benachrichtigung '
            . 'für nicht gelöschte Bestandsveranstaltungen. Leere Anmeldemodi werden auf native gesetzt. '
            . 'Bereits konfigurierte höhere Limits und ausgewählte Anmeldeformulare bleiben unverändert.';
    }

    public function executeUpdate(): bool
    {
        foreach (self::DEFAULTS as $field => $default) {
            $this->applyDefault($field, $default);
        }
        $this->applyRegistrationModeDefault();

        return true;
    }

    public function updateNecessary(): bool
    {
        foreach (self::DEFAULTS as $field => $default) {
            if ($this->hasValueBelowDefault($field, $default)) {
                return true;
            }
        }

        return $this->hasEmptyRegistrationMode();
    }

    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }

    private function applyDefault(string $field, int $default): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE);

        $queryBuilder
            ->update(self::TABLE)
            ->set($field, $default)
            ->where(
                $queryBuilder->expr()->eq(
                    'deleted',
                    $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
                ),
                $queryBuilder->expr()->lt(
                    $field,
                    $queryBuilder->createNamedParameter($default, ParameterType::INTEGER)
                )
            )
            ->executeStatement();
    }

    private function hasValueBelowDefault(string $field, int $default): bool
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
                    $field,
                    $queryBuilder->createNamedParameter($default, ParameterType::INTEGER)
                )
            )
            ->executeQuery()
            ->fetchOne();

        return (int)$count > 0;
    }

    private function applyRegistrationModeDefault(): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE);

        $queryBuilder
            ->update(self::TABLE)
            ->set(self::REGISTRATION_MODE_FIELD, self::REGISTRATION_MODE_DEFAULT)
            ->where(
                $queryBuilder->expr()->eq(
                    'deleted',
                    $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
                ),
                $queryBuilder->expr()->eq(
                    self::REGISTRATION_MODE_FIELD,
                    $queryBuilder->createNamedParameter('', ParameterType::STRING)
                )
            )
            ->executeStatement();
    }

    private function hasEmptyRegistrationMode(): bool
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
                $queryBuilder->expr()->eq(
                    self::REGISTRATION_MODE_FIELD,
                    $queryBuilder->createNamedParameter('', ParameterType::STRING)
                )
            )
            ->executeQuery()
            ->fetchOne();

        return (int)$count > 0;
    }
}
