<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

$tempPagesColumns = array(

    'tx_hgontemplate_contactperson' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_pages.tx_hgontemplate_contactperson',
        'config' => array(
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_rkwauthors_domain_model_authors',
            'foreign_table_where' => 'ORDER BY tx_rkwauthors_domain_model_authors.last_name ASC',
            'size' => 10,
            'autoSizeMax' => 30,
            'maxitems' => 1,
            'minitems' => 0,
            'multiple' => 0,
        ),
    ),
    'tx_hgontemplate_article' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_pages.tx_hgontemplate_article',
        'config' => array(
            'items' => array(
                array('LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_pages.pleaseChoose', 0),
            ),
            'type' => 'select',
            'renderType' => 'selectSingle',
            'foreign_table' => 'tx_hgonpayment_domain_model_article',
            'foreign_table_where' => 'AND tx_hgonpayment_domain_model_article.hidden = 0 AND tx_hgonpayment_domain_model_article.deleted = 0 ORDER BY tx_hgonpayment_domain_model_article.name ASC',
        ),
    ),

    // re-add after removing in RkwBasics for TYPO3 >= 8
    'tx_rkwbasics_article_image' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_basics/Resources/Private/Language/locallang_db.xlf:tx_rkwbasics_domain_model_pages.tx_rkwbasics_article_image',

        'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
            'txRkwBasicsArticleImage',
            [
                'maxitems' => 1,

                // Use the imageoverlayPalette instead of the basicoverlayPalette
                'foreign_types' => [
                    '0' => [
                        'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                        'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                        'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                        'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                        'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                        'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                    ],
                ],

            ],
            $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
        ),
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
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'pages',
    $tempPagesColumns
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('pages', 'tx_rkwbasics_extended','tx_hgontemplate_contactperson','after:tx_rkwprojects_project_uid');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('pages', 'tx_rkwbasics_extended2','tx_hgontemplate_article','after:tx_hgontemplate_contactperson');

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

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'tx_rkwbasics_article_image',
    '',
    'before:abstract'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'tx_rkwbasics_teaser_text',
    '',
    'before:abstract'
);



\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile('hgon_template', 'Themes/_Websites/LibellenHessen/Configuration/TsConfig/TsConfig.typoscript', 'HGON Template - Theme: Libellen');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile('hgon_template', 'Themes/_Websites/RebhuhnHessen/Configuration/TsConfig/TsConfig.typoscript', 'HGON Template - Theme: Rebhuhn');

