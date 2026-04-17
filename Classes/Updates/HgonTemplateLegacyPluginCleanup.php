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

#[UpgradeWizard('hgonTemplateLegacyPluginCleanup')]
final class HgonTemplateLegacyPluginCleanup implements UpgradeWizardInterface, RepeatableInterface
{
    private const EXT_KEY = 'hgon_template';

    public function getTitle(): string
    {
        return 'HGON Template: alte Projekt-Plugins loeschen';
    }

    public function getDescription(): string
    {
        return 'Entfernt veraltete Projekt-Inhaltselemente aus tt_content.';
    }

    public function executeUpdate(): bool
    {
        $signatures = $this->getLegacyPluginSignatures();
        if ($signatures === []) {
            return true;
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');

        $queryBuilder
            ->update('tt_content')
            ->set('deleted', 1)
            ->set('tstamp', time())
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->in('CType', $queryBuilder->createNamedParameter($signatures, ArrayParameterType::STRING)),
                    $queryBuilder->expr()->and(
                        $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('list')),
                        $queryBuilder->expr()->in('list_type', $queryBuilder->createNamedParameter($signatures, ArrayParameterType::STRING))
                    )
                )
            )
            ->executeStatement();

        return true;
    }

    public function updateNecessary(): bool
    {
        $signatures = $this->getLegacyPluginSignatures();
        if ($signatures === []) {
            return false;
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');

        $count = $queryBuilder
            ->count('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->in('CType', $queryBuilder->createNamedParameter($signatures, ArrayParameterType::STRING)),
                    $queryBuilder->expr()->and(
                        $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('list')),
                        $queryBuilder->expr()->in('list_type', $queryBuilder->createNamedParameter($signatures, ArrayParameterType::STRING))
                    )
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

    private function getLegacyPluginSignatures(): array
    {
        $extensionName = str_replace(' ', '', ucwords(str_replace('_', ' ', self::EXT_KEY)));
        $pluginNames = [
            'ProjectPartner',
            'ProjectTeaser',
        ];

        return array_map(
            static fn (string $pluginName): string => strtolower($extensionName . '_' . $pluginName),
            $pluginNames
        );
    }
}
