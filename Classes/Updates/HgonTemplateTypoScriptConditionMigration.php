<?php

namespace HGON\HgonTemplate\Updates;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Migrates deprecated TypoScript conditions in sys_template records.
 */
#[UpgradeWizard('hgonTemplateTypoScriptConditionMigration')]
final class HgonTemplateTypoScriptConditionMigration implements UpgradeWizardInterface
{
    public function getTitle(): string
    {
        return 'HGON Template: migrate deprecated TypoScript conditions';
    }

    public function getDescription(): string
    {
        return 'Rewrites deprecated [globalVar = TSFE:id ...] and [globalVar = GP:L ...] TypoScript conditions '
            . 'to TYPO3 v13-compatible syntax in sys_template constants/config.';
    }

    public function executeUpdate(): bool
    {
        $connection = $this->getConnection();

        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder
            ->select('uid', 'constants', 'config')
            ->from('sys_template')
            ->where(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->like(
                        'constants',
                        $queryBuilder->createNamedParameter('%globalVar = TSFE:id%')
                    ),
                    $queryBuilder->expr()->like(
                        'constants',
                        $queryBuilder->createNamedParameter('%globalVar = GP:L%')
                    ),
                    $queryBuilder->expr()->like(
                        'config',
                        $queryBuilder->createNamedParameter('%globalVar = TSFE:id%')
                    ),
                    $queryBuilder->expr()->like(
                        'config',
                        $queryBuilder->createNamedParameter('%globalVar = GP:L%')
                    )
                )
            );

        $rows = $queryBuilder->executeQuery()->fetchAllAssociative();

        foreach ($rows as $row) {
            $constants = (string)($row['constants'] ?? '');
            $config = (string)($row['config'] ?? '');

            $newConstants = $this->rewriteConditions($constants);
            $newConfig = $this->rewriteConditions($config);

            if ($newConstants === $constants && $newConfig === $config) {
                continue;
            }

            $connection->update(
                'sys_template',
                [
                    'constants' => $newConstants,
                    'config' => $newConfig,
                ],
                ['uid' => (int)$row['uid']]
            );
        }

        return true;
    }

    public function updateNecessary(): bool
    {
        $connection = $this->getConnection();

        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder
            ->select('uid')
            ->from('sys_template')
            ->where(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->like(
                        'constants',
                        $queryBuilder->createNamedParameter('%globalVar = TSFE:id%')
                    ),
                    $queryBuilder->expr()->like(
                        'constants',
                        $queryBuilder->createNamedParameter('%globalVar = GP:L%')
                    ),
                    $queryBuilder->expr()->like(
                        'constants',
                        $queryBuilder->createNamedParameter("%request.getQueryParams()['L'] ==%")
                    ),
                    $queryBuilder->expr()->like(
                        'config',
                        $queryBuilder->createNamedParameter('%globalVar = TSFE:id%')
                    ),
                    $queryBuilder->expr()->like(
                        'config',
                        $queryBuilder->createNamedParameter('%globalVar = GP:L%')
                    ),
                    $queryBuilder->expr()->like(
                        'config',
                        $queryBuilder->createNamedParameter("%request.getQueryParams()['L'] ==%")
                    )
                )
            )
            ->setMaxResults(1);

        return (bool)$queryBuilder->executeQuery()->fetchOne();
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    private function rewriteConditions(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        $value = preg_replace(
            '/\\[\\s*globalVar\\s*=\\s*TSFE:id\\s*=\\s*(\\d+)\\s*\\]/i',
            '[page[\"uid\"] == $1]',
            $value
        );

        $value = preg_replace(
            '/\\[\\s*globalVar\\s*=\\s*GP:L\\s*=\\s*(\\d+)\\s*\\]/i',
            "[(request.getQueryParams()['L'] ?? 0) == $1]",
            $value
        );

        $value = preg_replace(
            '/\\[\\s*request\\.getQueryParams\\(\\)\\[\\\'L\\\'\\]\\s*==\\s*(\\d+)\\s*\\]/i',
            "[(request.getQueryParams()['L'] ?? 0) == $1]",
            $value
        );

        return $value;
    }

    private function getConnection(): Connection
    {
        /** @var Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_template');

        return $connection;
    }
}
