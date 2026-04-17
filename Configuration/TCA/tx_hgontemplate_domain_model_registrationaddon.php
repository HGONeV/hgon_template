<?php
defined('TYPO3') or die();

$GLOBALS['TCA']['tx_hgontemplate_domain_model_registrationaddon'] = [
    'ctrl' => [
        'hideTable' => true,
        'title' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_registrationaddon',
        'label' => 'title',
        'rootLevel' => 0,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'title,description',
        'iconfile' => 'EXT:hgon_template/Resources/Public/Icons/tx_hgontemplate_domain_model_eventculinary.gif',
    ],
    'types' => [
        '1' => ['showitem' => 'hidden, title, description, quantity, unit_price, selected_date'],
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'title' => [
            'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_registrationaddon.title',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'description' => [
            'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_registrationaddon.description',
            'config' => [
                'type' => 'text',
                'readOnly' => true,
            ],
        ],
        'quantity' => [
            'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_registrationaddon.quantity',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
            ],
        ],
        'unit_price' => [
            'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_registrationaddon.unit_price',
            'config' => [
                'type' => 'number',
                'format' => 'decimal',
                'readOnly' => true,
            ],
        ],
        'selected_date' => [
            'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_registrationaddon.selected_date',
            'config' => [
                'type' => 'datetime',
                'readOnly' => true,
            ],
        ],
        'registration' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'culinary' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
];
