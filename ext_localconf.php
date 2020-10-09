<?php
defined('TYPO3_MODE') || die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['hgon_default'] = 'EXT:hgon_template/Configuration/Yaml/RTE/HgonDefault.yaml';

/*
// locallang override FE
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:rkw_newsletter/Resources/Private/Language/locallang.xlf'][] = 'EXT:hgon_template/Resources/Private/Language/locallang_rkwnewsletter.xlf';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:rkw_events/Resources/Private/Language/locallang.xlf'][] = 'EXT:hgon_template/Resources/Private/Language/locallang_rkwevents.xlf';

// locallang override BE
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:rkw_authors/Resources/Private/Language/locallang_db.xlf'][] = 'EXT:hgon_template/Resources/Private/Language/locallang_rkwauthors_db.xlf';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:rkw_basics/Resources/Private/Language/locallang_db.xlf'][] = 'EXT:hgon_template/Resources/Private/Language/locallang_rkwbasics_db.xlf';
*/


call_user_func(
    function($extKey)
    {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'PageHighlight',
            [
                'Standard' => 'pageHighlight'
            ],
            // non-cacheable actions
            [
                'Standard' => 'pageHighlight'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'RandomAuthor',
            [
                'Standard' => 'randomAuthor'
            ],
            // non-cacheable actions
            [
                'Standard' => 'randomAuthor'
            ]
        );

        /*
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'ProjectTeaser',
            [
                'Standard' => 'projectTeaser'
            ],
            // non-cacheable actions
            [
                'Standard' => 'projectTeaser'
            ]
        );
        */

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'SidebarContactPerson',
            [
                'Standard' => 'sidebarContactPerson'
            ],
            // non-cacheable actions
            [
                'Standard' => 'sidebarContactPerson'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'SiblingPagesOverview',
            [
                'Standard' => 'siblingPagesOverview'
            ],
            // non-cacheable actions
            [
                'Standard' => 'siblingPagesOverview'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'ChildrenPagesOverview',
            [
                'Standard' => 'childrenPagesOverview'
            ],
            // non-cacheable actions
            [
                'Standard' => 'childrenPagesOverview'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'PageSlider',
            [
                'Standard' => 'pageSlider'
            ],
            // non-cacheable actions
            [
                'Standard' => 'pageSlider'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'DonationForm',
            [
                'Standard' => 'donationForm'
            ],
            // non-cacheable actions
            [
                'Standard' => 'donationForm'
            ]
        );

        /*
         * -> Moved to HGON Donation
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'SupportOptions',
            [
                'Standard' => 'supportOptions'
            ],
            // non-cacheable actions
            [
                'Standard' => 'supportOptions'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'SupportOptionsLight',
            [
                'Standard' => 'supportOptionsLight'
            ],
            // non-cacheable actions
            [
                'Standard' => 'supportOptionsLight'
            ]
        );
        */

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'SixReasons',
            [
                'Standard' => 'sixReasons'
            ],
            // non-cacheable actions
            [
                'Standard' => 'sixReasons'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'DidYouKnow',
            [
                'Standard' => 'didYouKnow'
            ],
            // non-cacheable actions
            [
                'Standard' => 'didYouKnow'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'Maps',
            [
                'Standard' => 'maps'
            ],
            // non-cacheable actions
            [
                'Standard' => 'maps'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'ProjectPartner',
            [
                'Standard' => 'projectPartner'
            ],
            // non-cacheable actions
            [
                'Standard' => 'projectPartner'
            ]
        );

        // ***************
        // NEWS
        // ***************

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'ShowRelatedSidebar',
            [
                'News' => 'showRelatedSidebar'
            ],
            // non-cacheable actions
            [
                'News' => 'showRelatedSidebar'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'JournalHighlight',
            [
                'News' => 'journalHighlight'
            ],
            // non-cacheable actions
            [
                'News' => 'journalHighlight'
            ]
        );


        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'Journal',
            [
                'News' => 'journal'
            ],
            // non-cacheable actions
            [
                'News' => 'journal'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'Header',
            [
                'News' => 'header'
            ],
            // non-cacheable actions
            [
                'News' => 'header'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'Sidebar',
            [
                'News' => 'sidebar'
            ],
            // non-cacheable actions
            [
                'News' => 'sidebar'
            ]
        );




        // ***************
        // Article
        // ***************

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'ShowArticleFromPages',
            [
                'Article' => 'showArticleFromPages, newOrder, createOrder'
            ],
            // non-cacheable actions
            [
                'Article' => 'showArticleFromPages, newOrder, createOrder'
            ]
        );



        /* TEST
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.RkwEvents',
            'Reservation',
            [
                'EventReservation' => 'new, create, createAlternative, update, delete, remove, optIn, edit, end',
            ],
            // non-cacheable actions
            [
                'EventReservation' => 'new, create, createAlternative, update, delete, remove, optIn, edit, end',
            ]
        );*/


        // ***************
        // RkwEvents (we need a extra plugin for reservations)
        // -> we can't create a simple marker with controller and actions for eventReservation. Throws an error in relation of the multiple use of controller / actions / plugins on the same page
        // ***************

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.RkwEvents',
            'Reservation',
            [
                'EventReservation' => 'new, create, createAlternative, update, delete, remove, optIn, edit, end',
            ],
            // non-cacheable actions
            [
                'EventReservation' => 'new, create, createAlternative, update, delete, remove, optIn, edit, end',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.RkwEvents',
            'Upcoming',
            array(
                'Event' => 'upcoming'
            ),
            // non-cacheable actions
            array(
                'Event' => 'upcoming'
            )
        );


        // caching
        if( !is_array($GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey] ) ) {
            $GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey] = array();
        }
        if( !isset($GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['frontend'] ) ) {
            $GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['frontend'] = 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend';
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
                'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
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
    $_EXTKEY
);

/**
 * Page TSconfig
 */
$pageTSconfig = \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl(
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY)
    . 'Configuration/TsConfig/TsConfig.typoscript'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($pageTSconfig);

// <INCLUDE_TYPOSCRIPT: source="FILE:EXT:hgon_template/Configuration/TsConfig/TsConfig.typoscript">