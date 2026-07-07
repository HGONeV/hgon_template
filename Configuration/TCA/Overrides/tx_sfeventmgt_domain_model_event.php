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
    'tx_hgontemplate_online_event' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_sfeventmgt_domain_model_event.tx_hgontemplate_online_event',
        'onChange' => 'reload',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => 0,
            'items' => [
                [
                    'label' => '',
                    'invertStateDisplay' => false,
                ],
            ],
        ],
    ],
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
    'tx_hgontemplate_registration_mode' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_sfeventmgt_domain_model_event.tx_hgontemplate_registration_mode',
        'description' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_sfeventmgt_domain_model_event.tx_hgontemplate_registration_mode.description',
        'onChange' => 'reload',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [
                    'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_sfeventmgt_domain_model_event.tx_hgontemplate_registration_mode.native',
                    'value' => 'native',
                ],
                [
                    'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_sfeventmgt_domain_model_event.tx_hgontemplate_registration_mode.form',
                    'value' => 'form',
                ],
            ],
            'default' => 'native',
        ],
    ],
    'tx_hgontemplate_registration_form' => [
        'exclude' => 1,
        'displayCond' => 'FIELD:tx_hgontemplate_registration_mode:=:form',
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_sfeventmgt_domain_model_event.tx_hgontemplate_registration_form',
        'description' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_sfeventmgt_domain_model_event.tx_hgontemplate_registration_form.description',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'itemsProcFunc' => \HGON\HgonTemplate\Tca\ItemsProcFunc\FormDefinitionItems::class . '->addEventRegistrationForms',
            'default' => '',
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('tx_sfeventmgt_domain_model_event', $tempColumns);

$GLOBALS['TCA']['tx_sfeventmgt_domain_model_event']['columns']['location']['displayCond'] =
    'FIELD:tx_hgontemplate_online_event:!=:1';
$GLOBALS['TCA']['tx_sfeventmgt_domain_model_event']['columns']['room']['displayCond'] =
    'FIELD:tx_hgontemplate_online_event:!=:1';

ExtensionManagementUtility::addFieldsToPalette(
    'tx_sfeventmgt_domain_model_event',
    'titleTopEvent',
    'tx_hgontemplate_online_event',
    'before:title'
);
ExtensionManagementUtility::addFieldsToPalette(
    'tx_sfeventmgt_domain_model_event',
    'titleTopEvent',
    'tx_hgontemplate_event_type',
    'before:title'
);
$GLOBALS['TCA']['tx_sfeventmgt_domain_model_event']['palettes']['titleTopEvent']['showitem'] =
    'tx_hgontemplate_event_type, tx_hgontemplate_online_event, --linebreak--, title, top_event';
ExtensionManagementUtility::addFieldsToPalette(
    'tx_sfeventmgt_domain_model_event',
    'hgonRegistration',
    'tx_hgontemplate_registration_mode, tx_hgontemplate_registration_form'
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tx_sfeventmgt_domain_model_event',
    '--palette--;;hgonRegistration',
    '',
    'before:slug'
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tx_sfeventmgt_domain_model_event',
    '--div--;LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_sfeventmgt_domain_model_event.tabs.hgon, tx_hgontemplate_eventculinary',
    '',
    'after:registration_fields'
);
