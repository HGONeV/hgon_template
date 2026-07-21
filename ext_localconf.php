<?php

use DERHANSEN\SfEventMgt\Controller\EventController;
use HGON\HgonTemplate\Routing\Aspect\EventMonthMapper;
use HGON\HgonTemplate\Routing\Aspect\SpeciesSlugMapper;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die("Access denied.");

$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['hgon_default'] = 'EXT:hgon_template/Configuration/Yaml/RTE/HgonDefault.yaml';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'][1727540190] = 'EXT:hgon_template/Resources/Private/Extension/HgonTemplate/Templates/Mail/';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['partialRootPaths'][1727540190] = 'EXT:hgon_template/Resources/Private/Extension/HgonTemplate/Partials/';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths'][1727540190] = 'EXT:hgon_template/Resources/Private/Extension/HgonTemplate/Layouts/';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['EventMonth'] = EventMonthMapper::class;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['HgonSpeciesSlug'] = SpeciesSlugMapper::class;

ExtensionManagementUtility::addPageTSConfig(
    "@import 'EXT:hgon_template/Configuration/TsConfig/TsConfig.typoscript'"
);

ExtensionUtility::configurePlugin(
    'SfEventMgt',
    'Pieventlist',
    [
        EventController::class => ['list'],
    ],
    [
        EventController::class => ['list'],
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);


call_user_func(
    function($extKey)
    {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'PageHighlight',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'pageHighlight'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'pageHighlight'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'RandomAuthor',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'randomAuthor'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'randomAuthor'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'SidebarContactPerson',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'sidebarContactPerson'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'sidebarContactPerson'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'SiblingPagesOverview',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'siblingPagesOverview'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'siblingPagesOverview'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'ChildrenPagesOverview',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'childrenPagesOverview'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'childrenPagesOverview'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'PageSlider',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'pageSlider'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'pageSlider'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'DonationForm',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'donationForm'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'donationForm'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'SixReasons',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'sixReasons'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'sixReasons'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'DidYouKnow',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'didYouKnow'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'didYouKnow'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'Maps',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'maps'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'maps'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'AuthorList',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'authorList'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'authorList'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        // ***************
        // NEWS
        // ***************

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'ShowRelatedSidebar',
            [
                \HGON\HgonTemplate\Controller\NewsController::class => 'showRelatedSidebar'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\NewsController::class => 'showRelatedSidebar'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'JournalHighlight',
            [
                \HGON\HgonTemplate\Controller\NewsController::class => 'journalHighlight'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\NewsController::class => 'journalHighlight'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );


        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'Journal',
            [
                \HGON\HgonTemplate\Controller\NewsController::class => 'journal'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\NewsController::class => 'journal'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'Header',
            [
                \HGON\HgonTemplate\Controller\NewsController::class => 'header'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\NewsController::class => 'header'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'Sidebar',
            [
                \HGON\HgonTemplate\Controller\NewsController::class => 'sidebar'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\NewsController::class => 'sidebar'
            ],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        // caching
        $cacheConfigurations =& $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'];
        if (!isset($cacheConfigurations[$extKey]) || !is_array($cacheConfigurations[$extKey])) {
            $cacheConfigurations[$extKey] = [];
        }
        $cacheConfigurations[$extKey]['frontend'] ??= VariableFrontend::class;
        $cacheConfigurations[$extKey]['options'] ??= ['defaultLifetime' => 3600];
        $cacheConfigurations[$extKey]['groups'] ??= ['pages'];


        // set logger
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['HGON']['HgonTemplate']['writerConfiguration'] = array(

            // configuration for WARNING severity, including all
            // levels with higher severity (ERROR, CRITICAL, EMERGENCY)
            \TYPO3\CMS\Core\Log\LogLevel::WARNING => array(
                // add a FileWriter
                \TYPO3\CMS\Core\Log\Writer\FileWriter::class => array(
                    // configuration for the writer
                    'logFile' => 'var/log/tx_hgontemplate.log'
                )
            ),
        );

    },
    'hgon_template'
);
