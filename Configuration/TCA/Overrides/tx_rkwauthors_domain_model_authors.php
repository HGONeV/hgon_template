<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

$tempColumns = array(

    'tx_hgontemplate_short_description' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_authors.tx_hgontemplate_short_description',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],

    'tx_hgontemplate_longer_description' => [
        'exclude' => true,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_authors.tx_hgontemplate_longer_description',
        'config' => [
            'type' => 'text',
            'rows' => 42,
        ],
        'defaultExtras' => 'richtext[]:rte_transform[flag=rte_enabled|mode=ts_css]',
    ],


);
// Add TCA
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tx_rkwauthors_domain_model_authors',
    $tempColumns
);

$GLOBALS['TCA']['tx_rkwauthors_domain_model_authors']['types']['1']['showitem'] = str_replace('function_description,', 'function_description, tx_hgontemplate_short_description, tx_hgontemplate_longer_description,', $GLOBALS['TCA']['tx_rkwauthors_domain_model_authors']['types']['1']['showitem']);



