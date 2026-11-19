<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use RuntimeException;
use Throwable;
use TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Form\Mvc\Configuration\YamlSource;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Adds the MP3 upload and upload cleanup to the editable AKH form definition.
 *
 * Before changing the editor-managed form, the exact source file is copied to
 * the same FAL folder and verified using SHA-256. The backup deliberately uses
 * the extension ".backup" so that TYPO3 does not detect it as another form.
 */
#[UpgradeWizard('hgonTemplateAkhMp3UploadMigration')]
final class HgonTemplateAkhMp3UploadMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const FORM_PERSISTENCE_IDENTIFIER = '1:/form_definitions/aKHMeldebogen.form.yaml';
    private const FORM_IDENTIFIER = 'aKHMeldebogen';
    private const DOCUMENT_UPLOAD_IDENTIFIER = 'fileupload-1';
    private const MP3_UPLOAD_IDENTIFIER = 'fileupload-mp3';
    private const BACKUP_MARKER = 'before-hgonTemplateAkhMp3UploadMigration';

    public function __construct(
        private readonly ResourceFactory $resourceFactory,
        private readonly YamlSource $yamlSource,
    ) {}

    public function getTitle(): string
    {
        return 'HGON Template: MP3-Upload zum AKH-Meldebogen hinzufügen';
    }

    public function getDescription(): string
    {
        return 'Ergänzt den AKH-Meldebogen um einen MP3-Upload bis 5 MB. '
            . 'Nur die Empfänger-Mail erhält Upload-Anhänge; anschließend werden die Uploads gelöscht. '
            . 'Vor der Änderung wird die ursprüngliche Formulardatei im selben Ordner gesichert und geprüft.';
    }

    public function executeUpdate(): bool
    {
        $file = $this->getFormFile();
        if ($file === null) {
            throw new RuntimeException(
                sprintf('Die Formulardatei %s wurde nicht gefunden.', self::FORM_PERSISTENCE_IDENTIFIER)
            );
        }

        $formDefinition = $this->yamlSource->load([$file]);
        $migratedFormDefinition = $this->migrateFormDefinition($formDefinition);

        if ($migratedFormDefinition !== $formDefinition) {
            $originalContents = $file->getContents();
            $backupFile = $this->createVerifiedBackup($file, $originalContents);
            $this->saveAndVerifyMigration($file, $backupFile, $originalContents, $migratedFormDefinition);
        }

        return true;
    }

    public function updateNecessary(): bool
    {
        $file = $this->getFormFile();
        if ($file === null) {
            return false;
        }

        $formDefinition = $this->yamlSource->load([$file]);

        return $this->migrateFormDefinition($formDefinition) !== $formDefinition;
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    /**
     * Creates or reuses a byte-exact backup next to the form definition.
     *
     * The content hash is part of the file name. A retry therefore reuses the
     * backup belonging to the exact same source state. No form data is changed
     * unless the backup can be read back with the expected checksum.
     */
    private function createVerifiedBackup(File $formFile, string $originalContents): File
    {
        $parentFolder = $formFile->getParentFolder();
        if (!$parentFolder instanceof Folder) {
            throw new RuntimeException('Der Ordner der AKH-Formulardatei konnte nicht ermittelt werden.');
        }

        $originalHash = hash('sha256', $originalContents);
        $backupName = sprintf(
            '%s.%s.%s.backup',
            $formFile->getName(),
            self::BACKUP_MARKER,
            $originalHash
        );

        try {
            $backupFile = $parentFolder->hasFile($backupName)
                ? $parentFolder->getFile($backupName)
                : $formFile->copyTo($parentFolder, $backupName, DuplicationBehavior::CANCEL);
        } catch (Throwable $exception) {
            throw new RuntimeException(
                sprintf(
                    'Die Sicherung %s konnte nicht angelegt werden. Das AKH-Formular wurde nicht verändert.',
                    $backupName
                ),
                1795084441,
                $exception
            );
        }

        if (!$backupFile instanceof File
            || !hash_equals($originalHash, hash('sha256', $backupFile->getContents()))
        ) {
            throw new RuntimeException(
                sprintf(
                    'Die Sicherung %s konnte nicht verifiziert werden. Das AKH-Formular wurde nicht verändert.',
                    $backupName
                ),
                1795084442
            );
        }

        return $backupFile;
    }

    /**
     * Saves and reloads the migrated definition, restoring the backup on error.
     *
     * @param array<string, mixed> $migratedFormDefinition
     */
    private function saveAndVerifyMigration(
        File $formFile,
        File $backupFile,
        string $originalContents,
        array $migratedFormDefinition,
    ): void {
        try {
            $this->yamlSource->save($formFile, $migratedFormDefinition);
            $savedFormDefinition = $this->yamlSource->load([$formFile]);
            if ($savedFormDefinition !== $migratedFormDefinition) {
                throw new RuntimeException('Das gespeicherte AKH-Formular entspricht nicht der erwarteten Migration.');
            }
        } catch (Throwable $migrationException) {
            try {
                $this->restoreBackup($formFile, $backupFile, $originalContents);
            } catch (Throwable $restoreException) {
                throw new RuntimeException(
                    sprintf(
                        'Die Formularmigration ist fehlgeschlagen und die Sicherung %s konnte nicht automatisch wiederhergestellt werden.',
                        $backupFile->getCombinedIdentifier()
                    ),
                    1795084443,
                    $restoreException
                );
            }

            throw new RuntimeException(
                sprintf(
                    'Die Formularmigration ist fehlgeschlagen. Das Original wurde aus %s wiederhergestellt.',
                    $backupFile->getCombinedIdentifier()
                ),
                1795084444,
                $migrationException
            );
        }
    }

    /**
     * Restores the exact source bytes and verifies the restored checksum.
     */
    private function restoreBackup(File $formFile, File $backupFile, string $originalContents): void
    {
        $expectedHash = hash('sha256', $originalContents);
        $backupContents = $backupFile->getContents();
        if (!hash_equals($expectedHash, hash('sha256', $backupContents))) {
            throw new RuntimeException('Die Prüfsumme der AKH-Formularsicherung ist ungültig.');
        }

        $formFile->setContents($backupContents);
        if (!hash_equals($expectedHash, hash('sha256', $formFile->getContents()))) {
            throw new RuntimeException('Das wiederhergestellte AKH-Formular konnte nicht verifiziert werden.');
        }
    }

    private function migrateFormDefinition(array $formDefinition): array
    {
        if (($formDefinition['identifier'] ?? null) !== self::FORM_IDENTIFIER) {
            throw new RuntimeException(
                sprintf(
                    'Die Formulardatei %s enthält nicht das erwartete Formular %s.',
                    self::FORM_PERSISTENCE_IDENTIFIER,
                    self::FORM_IDENTIFIER
                )
            );
        }

        $mp3UploadFound = $this->configureMp3Upload($formDefinition['renderables']);
        if (!$mp3UploadFound) {
            throw new RuntimeException(
                sprintf(
                    'Das Upload-Feld %s wurde im AKH-Meldebogen nicht gefunden.',
                    self::DOCUMENT_UPLOAD_IDENTIFIER
                )
            );
        }

        $formDefinition['finishers'] = $this->configureFinishers($formDefinition['finishers'] ?? []);

        return $formDefinition;
    }

    /**
     * @param list<array<string, mixed>> $renderables
     */
    private function configureMp3Upload(array &$renderables): bool
    {
        $this->removeMp3Uploads($renderables);

        foreach ($renderables as $index => &$renderable) {
            if (($renderable['identifier'] ?? null) === self::DOCUMENT_UPLOAD_IDENTIFIER) {
                array_splice($renderables, $index + 1, 0, [$this->getMp3UploadDefinition()]);
                return true;
            }

            if (isset($renderable['renderables']) && is_array($renderable['renderables'])) {
                if ($this->configureMp3Upload($renderable['renderables'])) {
                    return true;
                }
            }
        }
        unset($renderable);

        return false;
    }

    /**
     * @param list<array<string, mixed>> $renderables
     */
    private function removeMp3Uploads(array &$renderables): void
    {
        foreach ($renderables as $index => &$renderable) {
            if (($renderable['identifier'] ?? null) === self::MP3_UPLOAD_IDENTIFIER) {
                unset($renderables[$index]);
                continue;
            }

            if (isset($renderable['renderables']) && is_array($renderable['renderables'])) {
                $this->removeMp3Uploads($renderable['renderables']);
            }
        }
        unset($renderable);

        $renderables = array_values($renderables);
    }

    /**
     * @return array<string, mixed>
     */
    private function getMp3UploadDefinition(): array
    {
        return [
            'properties' => [
                'saveToFileMount' => '1:/user_upload/',
                'allowedMimeTypes' => [
                    'audio/mpeg',
                    'audio/mp3',
                ],
            ],
            'validators' => [
                [
                    'options' => [
                        'maximum' => '5M',
                    ],
                    'identifier' => 'FileSize',
                ],
            ],
            'type' => 'FileUpload',
            'identifier' => self::MP3_UPLOAD_IDENTIFIER,
            'label' => 'MP3-Tonaufnahme hochladen (maximal 5 MB)',
        ];
    }

    /**
     * @param list<array<string, mixed>> $finishers
     * @return list<array<string, mixed>>
     */
    private function configureFinishers(array $finishers): array
    {
        $configuredFinishers = [];
        $deleteUploadsInserted = false;

        foreach ($finishers as $finisher) {
            $identifier = $finisher['identifier'] ?? null;
            if ($identifier === 'DeleteUploads') {
                continue;
            }

            if ($identifier === 'EmailToSender') {
                $finisher['options']['attachUploads'] = false;
            } elseif ($identifier === 'EmailToReceiver') {
                $finisher['options']['attachUploads'] = true;
            }

            if (!$deleteUploadsInserted && in_array($identifier, ['Confirmation', 'Redirect'], true)) {
                $configuredFinishers[] = ['identifier' => 'DeleteUploads'];
                $deleteUploadsInserted = true;
            }

            $configuredFinishers[] = $finisher;
        }

        if (!$deleteUploadsInserted) {
            $configuredFinishers[] = ['identifier' => 'DeleteUploads'];
        }

        return $configuredFinishers;
    }

    private function getFormFile(): ?File
    {
        try {
            $file = $this->resourceFactory->getFileObjectFromCombinedIdentifier(self::FORM_PERSISTENCE_IDENTIFIER);

            return $file instanceof File ? $file : null;
        } catch (FileDoesNotExistException) {
            return null;
        }
    }
}
