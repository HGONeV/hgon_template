<?php

defined('TYPO3') or die('Access denied.');

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$GLOBALS['TCA']['tx_sfeventmgt_domain_model_event']['columns']['image']['label'] =
    'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_sfeventmgt_domain_model_event.image';
$GLOBALS['TCA']['tx_sfeventmgt_domain_model_event']['columns']['image']['config']['maxitems'] = 1;
$GLOBALS['TCA']['tx_sfeventmgt_domain_model_event']['columns']['custom_text']['label'] =
    'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_sfeventmgt_domain_model_event.custom_text';
unset($GLOBALS['TCA']['tx_sfeventmgt_domain_model_event']['ctrl']['descriptionColumn']);

foreach ($GLOBALS['TCA']['tx_sfeventmgt_domain_model_event']['types'] as &$typeConfig) {
    if (!isset($typeConfig['showitem'])) {
        continue;
    }

    $typeConfig['showitem'] = str_replace(
        '

                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                    rowDescription',
        '',
        $typeConfig['showitem']
    );
}
unset($typeConfig);

$tempColumns = [
    'tx_hgontemplate_event_type' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_sfeventmgt_domain_model_event.tx_hgontemplate_event_type',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [
                    'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_sfeventmgt_domain_model_event.tx_hgontemplate_event_type.standard',
                    'value' => 'standard',
                ],
                [
                    'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_sfeventmgt_domain_model_event.tx_hgontemplate_event_type.workgroup',
                    'value' => 'workgroup',
                ],
            ],
            'default' => 'standard',
        ],
    ],
    'tx_hgontemplate_eventculinary' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_sfeventmgt_domain_model_event.tx_hgontemplate_eventculinary',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_hgontemplate_domain_model_eventculinary',
            'foreign_field' => 'event',
            'foreign_sortby' => 'sorting',
            'appearance' => [
                'collapseAll' => true,
                'expandSingle' => true,
                'levelLinksPosition' => 'top',
                'showSynchronizationLink' => true,
                'showPossibleLocalizationRecords' => true,
                'showAllLocalizationLink' => true,
            ],
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('tx_sfeventmgt_domain_model_event', $tempColumns);
ExtensionManagementUtility::addFieldsToPalette(
    'tx_sfeventmgt_domain_model_event',
    'titleTopEvent',
    'tx_hgontemplate_event_type',
    'before:title'
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tx_sfeventmgt_domain_model_event',
    '--div--;LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_sfeventmgt_domain_model_event.tabs.hgon, tx_hgontemplate_eventculinary',
    '',
    'after:registration_fields'
);
