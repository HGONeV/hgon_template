<?php

use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;

defined('TYPO3') or die("Access denied.");

$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['hgon_default'] = 'EXT:hgon_template/Configuration/Yaml/RTE/HgonDefault.yaml';


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
            ]
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
            ]
        );

        /*
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'ProjectTeaser',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'projectTeaser'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'projectTeaser'
            ]
        );
        */

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'SidebarContactPerson',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'sidebarContactPerson'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'sidebarContactPerson'
            ]
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
            ]
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
            ]
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
            ]
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
            ]
        );

        /*
         * -> Moved to HGON Donation
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'SupportOptions',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'supportOptions'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'supportOptions'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'SupportOptionsLight',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'supportOptionsLight'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'supportOptionsLight'
            ]
        );
        */

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'SixReasons',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'sixReasons'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'sixReasons'
            ]
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
            ]
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
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'ProjectPartner',
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'projectPartner'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\StandardController::class => 'projectPartner'
            ]
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
            ]
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
            ]
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
            ]
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
            ]
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
            ]
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
            ]
        );




        // ***************
        // Article
        // ***************

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            $extKey,
            'ShowArticleFromPages',
            [
                \HGON\HgonTemplate\Controller\ArticleController::class => 'showArticleFromPages, newOrder, createOrder'
            ],
            // non-cacheable actions
            [
                \HGON\HgonTemplate\Controller\ArticleController::class => 'showArticleFromPages, newOrder, createOrder'
            ]
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
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwBasics']['writerConfiguration'] = array(

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


        // for content slide
        $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ',subtitle,tx_rkwbasics_article_image,tx_hgontemplate_contactperson,';

        // FormFramework Hooks
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterBuildingFinished']['1575298962'] = HGON\HgonTemplate\Hooks\FormFramework\AfterBuildingFinishedHook::class;

    },
    'hgon_template'
);

/**
 * Page TSconfig
 */
$pageTSconfig = \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl(
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('hgon_template')
    . 'Configuration/TsConfig/TsConfig.typoscript'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($pageTSconfig);

// <INCLUDE_TYPOSCRIPT: source="FILE:EXT:hgon_template/Configuration/TsConfig/TsConfig.typoscript">
