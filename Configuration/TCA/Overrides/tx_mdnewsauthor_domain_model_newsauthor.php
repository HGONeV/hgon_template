<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

$temporaryColumns = [
    'phone2' => [
        'exclude' => true,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_mdnewsauthor_domain_model_newsauthor.phone2',
        'config' => [
            'type' => 'input',
            'size' => 30,
        ],
    ],
    'tx_hgontemplate_short_description' => [
        'exclude' => true,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_mdnewsauthor_domain_model_newsauthor.tx_hgontemplate_short_description',
        'config' => [
            'type' => 'text',
            'cols' => 40,
            'rows' => 5,
            'enableRichtext' => true,
            'richtextConfiguration' => 'default',
        ],
    ],
    'tx_hgontemplate_longer_description' => [
        'exclude' => true,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_mdnewsauthor_domain_model_newsauthor.tx_hgontemplate_longer_description',
        'config' => [
            'type' => 'text',
            'cols' => 40,
            'rows' => 15,
            'enableRichtext' => true,
            'richtextConfiguration' => 'default',
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('tx_mdnewsauthor_domain_model_newsauthor', $temporaryColumns);
ExtensionManagementUtility::addFieldsToPalette(
    'tx_mdnewsauthor_domain_model_newsauthor',
    'palette_contact',
    'phone2',
    'after:phone'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'tx_mdnewsauthor_domain_model_newsauthor',
    'tx_hgontemplate_short_description,tx_hgontemplate_longer_description',
    '',
    'after:bio'
);
