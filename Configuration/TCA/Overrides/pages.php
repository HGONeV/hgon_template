<?php

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die("Access denied.");

$tempPagesColumns = array(

    'tx_hgontemplate_contactperson' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_pages.tx_hgontemplate_contactperson',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_rkwauthors_domain_model_authors',
            'foreign_table_where' => 'ORDER BY tx_rkwauthors_domain_model_authors.last_name ASC',
            'size' => 10,
            'autoSizeMax' => 30,
            'maxitems' => 1,
            'minitems' => 0,
        ],
    ],

    'tx_hgontemplate_article' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_pages.tx_hgontemplate_article',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [
                    'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_pages.pleaseChoose',
                    'value' => 0,
                ],
            ],
            'foreign_table' => 'tx_hgonpayment_domain_model_article',
            'foreign_table_where' =>
                'AND tx_hgonpayment_domain_model_article.hidden = 0 ' .
                'AND tx_hgonpayment_domain_model_article.deleted = 0 ' .
                'ORDER BY tx_hgonpayment_domain_model_article.name ASC',
        ],
    ],

    // re-add after removing in RkwBasics for TYPO3 >= 8
    'tx_rkwbasics_article_image' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:rkw_basics/Resources/Private/Language/locallang_db.xlf:tx_rkwbasics_domain_model_pages.tx_rkwbasics_article_image',
        'config' => [
            'type' => 'file',
            'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] ?? 'jpg,jpeg,png,gif',
            'maxitems' => 1,
            'appearance' => [
                'createNewRelationLinkTitle' =>
                    'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference',
            ],
            'overrideChildTca' => [
                'types' => [
                    0 => [
                        'showitem' => '
                        --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette',
                    ],
                    File::FILETYPE_TEXT => [
                        'showitem' => '
                        --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette',
                    ],
                    File::FILETYPE_IMAGE => [
                        'showitem' => '
                        --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette',
                    ],
                    File::FILETYPE_AUDIO => [
                        'showitem' => '
                        --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette',
                    ],
                    File::FILETYPE_VIDEO => [
                        'showitem' => '
                        --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette',
                    ],
                    File::FILETYPE_APPLICATION => [
                        'showitem' => '
                        --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                        --palette--;;filePalette',
                    ],
                ],
            ],
        ],
    ],
    'tx_rkwbasics_teaser_text' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_basics/Resources/Private/Language/locallang_db.xlf:tx_rkwbasics_domain_model_pages.tx_rkwbasics_teaser_text',
        'config' => [
            'type' => 'text',
            'cols' => 40,
            'rows' => 15,
            'eval' => 'trim',
        ],
    ],

);
// Add TCA
ExtensionManagementUtility::addTCAcolumns(
    'pages',
    $tempPagesColumns
);

ExtensionManagementUtility::addFieldsToPalette('pages', 'tx_rkwbasics_extended','tx_hgontemplate_contactperson','after:subtitle');
ExtensionManagementUtility::addFieldsToPalette('pages', 'tx_rkwbasics_extended2','tx_hgontemplate_article','after:tx_hgontemplate_contactperson');

//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('pages', 'tx_rkwbasics_extended', 'tx_hgontemplate_contactperson, tx_hgontemplate_article');

// Add palette to new tab
// using renamed RKW tab instead
/*
$tempConfig = '--div--;LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_pages.tabs.hgon,--palette--;LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_pages.palettes.common;tx_hgontemplate_palette,';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    $tempConfig
);
*/

// Add field to the existing palette
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('pages', 'editorial','--linebreak--,tx_hgontemplate_contactperson','after:lastUpdated');

ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'tx_rkwbasics_article_image',
    '',
    'before:abstract'
);

ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'tx_rkwbasics_teaser_text',
    '',
    'before:abstract'
);



/**
 * Page TSconfig
 */

ExtensionManagementUtility::registerPageTSConfigFile(
    'hgon_template',
    'Configuration/TsConfig/TsConfig.typoscript',
    'Deine Ext: PageTSConfig'
);

ExtensionManagementUtility::registerPageTSConfigFile(
    'hgon_template',
    'Themes/_Websites/LibellenHessen/Configuration/TsConfig/TsConfig.typoscript',
    'HGON Template - Theme: Libellen'
);

ExtensionManagementUtility::registerPageTSConfigFile(
    'hgon_template',
    'Themes/_Websites/HeuschreckenHessen/Configuration/TsConfig/TsConfig.typoscript',
    'HGON Template - Theme: Rebhuhn'
);
