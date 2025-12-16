<?php
defined('TYPO3_MODE') || die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['hgon_default'] = 'EXT:hgon_template/Configuration/Yaml/RTE/HgonDefault.yaml';


call_user_func(
    function($extKey)
    {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
            'HGON.HgonTemplate',
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
        if( !is_array($GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey] ) ) {
            $GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey] = array();
        }
        if( !isset($GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['frontend'] ) ) {
            $GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['frontend'] = \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class;
        }
        if( !isset($GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['options'] ) ) {
            $GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['options'] = array('defaultLifetime' => 3600);
        }
        if( !isset($GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['groups'] ) ) {
            $GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['groups'] = array('pages');
        }

        // set logger
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwBasics']['writerConfiguration'] = array(

            // configuration for WARNING severity, including all
            // levels with higher severity (ERROR, CRITICAL, EMERGENCY)
            \TYPO3\CMS\Core\Log\LogLevel::WARNING => array(
                // add a FileWriter
                \TYPO3\CMS\Core\Log\Writer\FileWriter::class => array(
                    // configuration for the writer
                    'logFile' => 'typo3temp/logs/tx_hgontemplate.log'
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
