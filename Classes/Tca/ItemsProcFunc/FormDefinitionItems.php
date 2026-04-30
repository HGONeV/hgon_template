<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Tca\ItemsProcFunc;

use TYPO3\CMS\Core\Utility\GeneralUtility;

final class FormDefinitionItems
{
    public function addEventRegistrationForms(array &$config): void
    {
        $config['items'][] = [
            'label' => '---',
            'value' => '',
        ];

        $formsPath = GeneralUtility::getFileAbsFileName('EXT:hgon_template/Configuration/Yaml/FormFramework/Forms/');
        if ($formsPath === '' || !is_dir($formsPath)) {
            return;
        }

        $files = glob(rtrim($formsPath, '/') . '/*.form.yaml') ?: [];
        natcasesort($files);

        foreach ($files as $filePath) {
            $fileName = basename($filePath);
            $identifier = 'EXT:hgon_template/Configuration/Yaml/FormFramework/Forms/' . $fileName;

            $config['items'][] = [
                'label' => $fileName,
                'value' => $identifier,
            ];
        }
    }
}
