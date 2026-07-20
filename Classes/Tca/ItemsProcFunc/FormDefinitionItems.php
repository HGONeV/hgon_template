<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Tca\ItemsProcFunc;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class FormDefinitionItems
{
    public function addEventRegistrationForms(array &$config): void
    {
        $config['items'][] = [
            'label' => '---',
            'value' => '',
        ];

        $storage = GeneralUtility::makeInstance(ResourceFactory::class)->getStorageObject(1);
        if (!$storage->hasFolder('/form_definitions/')) {
            return;
        }

        $files = $storage->getFolder('/form_definitions/')->getFiles();
        usort($files, static fn (File $a, File $b): int => strnatcasecmp($a->getName(), $b->getName()));

        foreach ($files as $file) {
            $fileName = $file->getName();
            if (!str_ends_with($fileName, '.form.yaml')) {
                continue;
            }

            $config['items'][] = [
                'label' => $fileName,
                'value' => '1:' . $file->getIdentifier(),
            ];
        }
    }
}
