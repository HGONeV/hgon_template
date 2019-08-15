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
            'foreign_table' => 'tx_hgontemplate_domain_model_article',
            'foreign_table_where' => 'ANd tx_hgontemplate_domain_model_article.hidden = 0 AND tx_hgontemplate_domain_model_article.deleted = 0 ORDER BY tx_hgontemplate_domain_model_article.title ASC',
        ),
    ),
);
// Add TCA
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'pages',
    $tempPagesColumns
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('pages', 'tx_hgontemplate_palette', 'tx_hgontemplate_contactperson, tx_hgontemplate_article');

// Add palette to new tab
$tempConfig = '--div--;LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_pages.tabs.hgon,--palette--;LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_pages.palettes.common;tx_hgontemplate_palette,';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    $tempConfig
);
// Add field to the existing palette
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('pages', 'editorial','--linebreak--,tx_hgontemplate_contactperson','after:lastUpdated');


