<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}


$tempPagesColumns = array(

    // NEWS
    'tx_hgontemplate_news_select' => array(
        'onChange' => 'reload',
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_news_select',
        'config' => array(
            'items' => array(
                array('LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_news_select.none', 0),
                array('LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_news_select.automatic', 1),
                array('LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_news_select.manualSelect', 2),
            ),
            'type' => 'select',
            'renderType' => 'selectSingle',
        ),
    ),
    'tx_hgontemplate_news_count' => array(
        'displayCond' => 'FIELD:tx_hgontemplate_news_select:=:1',
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_news_count',
        'config' => array(
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim, required',
            'default' => 3
        ),
    ),
    'tx_hgontemplate_news_list' => array(
        'displayCond' => 'FIELD:tx_hgontemplate_news_select:=:2',
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_news_list',
        'config' => array(
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_news_domain_model_news',
            'foreign_table_where' => 'ORDER BY tx_news_domain_model_news.tstamp ASC',
            'size' => 10,
            'autoSizeMax' => 30,
            'maxitems' => 5,
            'minitems' => 0,
            'multiple' => 1,
        ),
    ),

    // ARTICLE
    'tx_hgontemplate_article_select' => array(
        'onChange' => 'reload',
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_article_count',
        'config' => array(
            'items' => array(
                array('LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_article_select.none', 0),
                array('LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_article_select.automatic', 1),
                array('LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_article_select.manualSelect', 2),
            ),
            'type' => 'select',
            'renderType' => 'selectSingle',
        ),
    ),
    'tx_hgontemplate_article_count' => array(
        'displayCond' => 'FIELD:tx_hgontemplate_article_select:=:1',
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_article_list',
        'config' => array(
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim, required',
            'default' => 1
        ),
    ),
    'tx_hgontemplate_article_list' => array(
        'displayCond' => 'FIELD:tx_hgontemplate_article_select:=:2',
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_article_list',
        'config' => array(
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_hgonpayment_domain_model_article',
            'foreign_table_where' => 'ORDER BY tx_hgonpayment_domain_model_article.name ASC',
            'size' => 10,
            'autoSizeMax' => 30,
            'maxitems' => 5,
            'minitems' => 0,
            'multiple' => 1,
        ),
    ),

    // EVENTS
    'tx_hgontemplate_event_select' => array(
        'onChange' => 'reload',
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_event_select',
        'config' => array(
            'items' => array(
                array('LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_event_select.none', 0),
                array('LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_event_select.automatic', 1),
                array('LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_event_select.manualSelect', 2),
            ),
            'type' => 'select',
            'renderType' => 'selectSingle',
        ),
    ),
    'tx_hgontemplate_event_count' => array(
        'displayCond' => 'FIELD:tx_hgontemplate_event_select:=:1',
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_event_list',
        'config' => array(
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim, required',
            'default' => 2
        ),
    ),
    'tx_hgontemplate_event_list' => array(
        'displayCond' => 'FIELD:tx_hgontemplate_event_select:=:2',
        'exclude' => 0,
        'label' => 'LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.tx_hgontemplate_event_list',
        'config' => array(
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_rkwevents_domain_model_event',
            'foreign_table_where' => 'ORDER BY tx_rkwevents_domain_model_event.tstamp ASC',
            'size' => 10,
            'autoSizeMax' => 30,
            'maxitems' => 4,
            'minitems' => 0,
            'multiple' => 1,
        ),
    ),
);
// Add TCA
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tx_rkwnewsletter_domain_model_newsletter',
    $tempPagesColumns
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('tx_rkwnewsletter_domain_model_newsletter', 'tx_hgontemplate_palette_news', 'tx_hgontemplate_news_select,tx_hgontemplate_news_count,tx_hgontemplate_news_list');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('tx_rkwnewsletter_domain_model_newsletter', 'tx_hgontemplate_palette_article', 'tx_hgontemplate_article_select,tx_hgontemplate_article_count,tx_hgontemplate_article_list');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('tx_rkwnewsletter_domain_model_newsletter', 'tx_hgontemplate_palette_events', 'tx_hgontemplate_event_select,tx_hgontemplate_event_count,tx_hgontemplate_event_list');

// Add palette to new tab
$tempConfig = '
                --div--;LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_pages.tabs.hgon,
                --palette--;LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.palettes.news;tx_hgontemplate_palette_news,
                --palette--;LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.palettes.article;tx_hgontemplate_palette_article,
                --palette--;LLL:EXT:hgon_template/Resources/Private/Language/locallang_db.xlf:tx_hgontemplate_domain_model_newsletter.palettes.events;tx_hgontemplate_palette_events
               ';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tx_rkwnewsletter_domain_model_newsletter',
    $tempConfig
);