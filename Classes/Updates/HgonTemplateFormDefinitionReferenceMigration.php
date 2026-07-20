<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use Doctrine\DBAL\ParameterType;
use RuntimeException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Moves persisted form references from extension or legacy fileadmin paths
 * to the editable shared form_definitions directory.
 */
#[UpgradeWizard('hgonTemplateFormDefinitionReferenceMigration')]
final class HgonTemplateFormDefinitionReferenceMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const CONTENT_TABLE = 'tt_content';
    private const EVENT_TABLE = 'tx_sfeventmgt_domain_model_event';

    public function getTitle(): string
    {
        return 'HGON Template: Formular-Referenzen nach fileadmin migrieren';
    }

    public function getDescription(): string
    {
        return 'Überführt bestehende Formular-Referenzen in Inhaltselementen und Veranstaltungen '
            . 'nach 1:/form_definitions/. Die zugehörigen YAML-Dateien müssen zuvor im fileadmin liegen.';
    }

    public function executeUpdate(): bool
    {
        $changes = $this->findChanges();
        if ($changes === []) {
            return true;
        }

        $this->ensureTargetFilesExist($changes);

        $connection = $this->getConnection(self::CONTENT_TABLE);
        $connection->beginTransaction();

        try {
            foreach ($changes as $change) {
                $connection->update(
                    $change['table'],
                    [$change['field'] => $change['value']],
                    ['uid' => $change['uid']],
                    [$change['field'] => ParameterType::STRING, 'uid' => ParameterType::INTEGER]
                );
            }
            $connection->commit();
        } catch (\Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }

        return true;
    }

    public function updateNecessary(): bool
    {
        return $this->findChanges() !== [];
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    /**
     * @return list<array{table: string, uid: int, field: string, value: string, sourceValue: string}>
     */
    private function findChanges(): array
    {
        $changes = $this->findChangesInTable(
            self::CONTENT_TABLE,
            'pi_flexform',
            'CType = :contentType',
            ['contentType' => 'form_formframework'],
            ['contentType' => ParameterType::STRING]
        );

        if ($this->tableExists(self::EVENT_TABLE)) {
            array_push(
                $changes,
                ...$this->findChangesInTable(
                    self::EVENT_TABLE,
                    'tx_hgontemplate_registration_form',
                    '1 = 1'
                )
            );
        }

        return $changes;
    }

    /**
     * @param array<string, mixed> $parameters
     * @param array<string, mixed> $types
     * @return list<array{table: string, uid: int, field: string, value: string, sourceValue: string}>
     */
    private function findChangesInTable(
        string $table,
        string $field,
        string $where,
        array $parameters = [],
        array $types = []
    ): array {
        $connection = $this->getConnection($table);
        $rows = $connection->fetchAllAssociative(
            sprintf('SELECT uid, %s FROM %s WHERE deleted = 0 AND %s', $field, $table, $where),
            $parameters,
            $types
        );
        $changes = [];

        foreach ($rows as $row) {
            $sourceValue = (string)$row[$field];
            $targetValue = str_replace(
                array_keys($this->getPathReplacements()),
                array_values($this->getPathReplacements()),
                $sourceValue
            );
            if ($targetValue === $sourceValue) {
                continue;
            }

            $changes[] = [
                'table' => $table,
                'uid' => (int)$row['uid'],
                'field' => $field,
                'value' => $targetValue,
                'sourceValue' => $sourceValue,
            ];
        }

        return $changes;
    }

    /**
     * @param list<array{table: string, uid: int, field: string, value: string, sourceValue: string}> $changes
     */
    private function ensureTargetFilesExist(array $changes): void
    {
        $missingFiles = [];
        foreach ($changes as $change) {
            foreach ($this->getPathReplacements() as $sourcePath => $targetPath) {
                if (!str_contains($change['sourceValue'], $sourcePath)) {
                    continue;
                }

                $fileName = basename($targetPath);
                $targetFile = Environment::getPublicPath() . '/fileadmin/form_definitions/' . $fileName;
                if (!is_file($targetFile)) {
                    $missingFiles[] = $fileName;
                }
            }
        }

        if ($missingFiles !== []) {
            throw new RuntimeException(
                'Die folgenden Formular-Dateien fehlen in public/fileadmin/form_definitions/: '
                . implode(', ', array_unique($missingFiles))
            );
        }
    }

    /**
     * @return array<string, string>
     */
    private function getPathReplacements(): array
    {
        $fileNames = [
            'aKHMeldebogen.form.yaml',
            'anmeldeformularVeranstaltungen.form.yaml',
            'anmeldungzur17JahrestagungderDGfO.form.yaml',
            'artpatenschaft.form.yaml',
            'birdnet.form.yaml',
            'contactForm.form.yaml',
            'fJ.form.yaml',
            'mentoringprogramm.form.yaml',
            'mitgliedschaftschenken.form.yaml',
            'mitgliedsformular.form.yaml',
            'shop-Formular_1.form.yaml',
            'veranstaltungsanmeldungRedaktionell.form.yaml',
            'zeitspende.form.yaml',
        ];
        $replacements = [];
        foreach ($fileNames as $fileName) {
            $replacements['EXT:hgon_template/Configuration/Yaml/FormFramework/Forms/' . $fileName]
                = '1:/form_definitions/' . $fileName;
        }
        $replacements['1:/user_upload/anmeldungzur17JahrestagungderDGfO.form.yaml']
            = '1:/form_definitions/anmeldungzur17JahrestagungderDGfO.form.yaml';
        $replacements['1:/user_upload/artpatenschaft.form.yaml']
            = '1:/form_definitions/artpatenschaft.form.yaml';

        return $replacements;
    }

    private function tableExists(string $table): bool
    {
        return $this->getConnection($table)->createSchemaManager()->tablesExist([$table]);
    }

    private function getConnection(string $table): Connection
    {
        /** @var Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);

        return $connection;
    }
}
