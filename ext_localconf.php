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



        // ***************
        // Article
        // ***************

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'HGON.HgonTemplate',
            'ShowArticleFromPages',
            [
                'Article' => 'showArticleFromPages'
            ],
            // non-cacheable actions
            [
                'Article' => 'showArticleFromPages'
            ]
        );


        // for content slide
        $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ',subtitle,tx_rkwbasics_article_image,tx_hgontemplate_contactperson,';

        // add to InstallTool options (otherwise the RkwEvents ajax calls will not work)
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[action]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[controller]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[event]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[filter]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[filter][time]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[filter][documentType]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[filter][workGroup]';

        // @toDo: Notwendig weil [FE][pageNotFoundOnCHashError] = true
        // -> ABER: Das muss doch auch anders gehen. Man kann doch unmöglich immer jedes Formularfeld hier ein- bzw nachtragen...
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[newEventReservation]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[newEventReservation][event]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[newEventReservation][salutation]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[newEventReservation][firstName]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[newEventReservation][lastName]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[newEventReservation][email]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[newEventReservation][company]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[newEventReservation][address]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[newEventReservation][zip]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[newEventReservation][city]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[newEventReservation][remark]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[terms]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_pi1[privacy]';

        // new plugin
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[action]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[controller]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[event]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[newEventReservation]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[newEventReservation][event]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[newEventReservation][salutation]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[newEventReservation][firstName]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[newEventReservation][lastName]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[newEventReservation][email]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[newEventReservation][company]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[newEventReservation][address]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[newEventReservation][zip]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[newEventReservation][city]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[newEventReservation][remark]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[terms]';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_rkwevents_rkweventsreservation[privacy]';

        /*
        // caching
        if( !is_array($GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey] ) ) {
            $GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey] = array();
        }
        // Hier ist der entscheidende Punkt! Es ist der Cache von Variablen gesetzt!
        if( !isset($GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['frontend'] ) ) {
            $GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['frontend'] = 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend';
        }

        if( !isset($GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['groups'] ) ) {
            $GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$extKey]['groups'] = array('pages');
        }
        */

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['encodeSpURL_postProc'][] =
            'EXT:extkey/Classes/Hooks/RealurlEncoding.php:In2code\Extkey\Hooks\RealurlEncoding->convert';
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['decodeSpURL_preProc'][] =
            'EXT:extkey/Classes/Hooks/RealurlDecoding.php:In2code\Extkey\Hooks\RealurlDecoding->convert';

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