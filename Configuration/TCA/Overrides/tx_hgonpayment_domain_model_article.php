<?php

use TYPO3\CMS\Core\Resource\File;

defined('TYPO3') or die("Access denied.");

$tempPagesColumns = [
    'tx_hgontemplate_subtitle' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_article.subtitle',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],
    'tx_hgontemplate_image' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_article.image',
        'config' => [
            'type' => 'file',
            'allowed' => 'jpg,jpeg,png,gif',
            'minitems' => 1,
            'maxitems' => 1,
            'overrideChildTca' => [
                'types' => [
                    File::FILETYPE_IMAGE => [
                        'showitem' => '
                        --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette',
                    ],
                ],
            ],
        ],
    ],
    'tx_hgontemplate_link' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_article.link',
        'config' => [
            'type' => 'link',
            'size' => 50,
            'eval' => 'trim',
            'fieldControl' => [
                'linkPopup' => [
                    'options' => [
                        'title' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header_link_formlabel',
                    ],
                ],
            ],
        ],
    ],
];

// Add TCA
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tx_hgonpayment_domain_model_article',
    $tempPagesColumns
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tx_hgonpayment_domain_model_article',
    'tx_hgontemplate_subtitle,tx_hgontemplate_image,tx_hgontemplate_link',
    '',
    'after:name'
);

