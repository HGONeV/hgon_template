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
            'foreign_table' => 'tx_mdnewsauthor_domain_model_newsauthor',
            'foreign_table_where' => 'ORDER BY tx_mdnewsauthor_domain_model_newsauthor.lastname ASC, tx_mdnewsauthor_domain_model_newsauthor.firstname ASC',
            'size' => 10,
            'autoSizeMax' => 30,
            'maxitems' => 1,
            'minitems' => 0,
        ],
    ],
    'tx_hgontemplate_article_image' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_pages.tx_hgontemplate_article_image',
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
    'tx_hgontemplate_teaser_text' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_pages.tx_hgontemplate_teaser_text',
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

ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--div--;LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_pages.tabs.hgon,' .
    'tx_hgondonation_project,tx_hgontemplate_contactperson,tx_hgontemplate_article_image,tx_hgontemplate_teaser_text'
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
