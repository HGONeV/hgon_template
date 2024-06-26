<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'PageHighlight',
            'HGON: Seiten-Highlight'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'RandomAuthor',
            'HGON: Zufälliger HGON-Autor mit Zitat'
        );

        /*
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'ProjectTeaser',
            'HGON: Projektauswahl anzeigen'
        );
        */

        /*
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'SidebarContactPerson',
            'HGON: Sidebar Kontaktperson'
        );
        */

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'SiblingPagesOverview',
            'HGON: Generiert Vorschau für Geschwisterseiten'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'ChildrenPagesOverview',
            'HGON: Generiert Vorschau für Unterseiten'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'PageSlider',
            'HGON: Slider Projekte (Seiten)'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'DonationForm',
            'HGON: Spendenformular'
        );

        /*
         * Moved to HGON Donation
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'SupportOptions',
            'HGON: Zeige Spenden-Otionen'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'SupportOptionsLight',
            'HGON: Zeige Spenden-Otionen (Mitglied & Geld)'
        );
        */

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'ContactForm',
            'HGON: Kontaktformular'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'SixReasons',
            'HGON: Sechs Gründe'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'DidYouKnow',
            'HGON: Wussten Sie schon?'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'Maps',
            'HGON: Zeigt HGON auf GoogleMaps'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'ProjectPartner',
            'HGON: Zeigt Projekt-Partner'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'AuthorList',
            'HGON: Teammitglieder (Liste)'
        );


        // ***************
        // NEWS
        // ***************

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'JournalHighlight',
            'HGON: Journal Highlights'
        );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'Journal',
            'HGON: Journal'
        );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'Header',
            'HGON: News Header'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'Sidebar',
            'HGON: News Sidebar'
        );



        // ***************
        // Article
        // ***************

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'HGON.HgonTemplate',
            'ShowArticleFromPages',
            'HGON: Bestellung (Artikel)'
        );


        // ***************
        // RKW Events
        // ***************
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwEvents',
            'Upcoming',
            'RKW Events: Zeigt bevorstehende Veranstaltungen'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwEvents',
            'Reservation',
            'RKW Events: Angepasstes Reservierungs-Plugin (HGON)'
        );

    },
    $_EXTKEY
);


// locallang override FE
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:rkw_newsletter/Resources/Private/Language/locallang.xlf'][] = 'EXT:hgon_template/Resources/Private/Language/locallang_rkwnewsletter.xlf';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:rkw_events/Resources/Private/Language/locallang.xlf'][] = 'EXT:hgon_template/Resources/Private/Language/locallang_rkwevents.xlf';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:sr_freecap/Resources/Private/Language/locallang.xlf'][] = 'EXT:hgon_template/Resources/Private/Language/locallang_srfreecap.xlf';

// locallang override BE
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:rkw_authors/Resources/Private/Language/locallang_db.xlf'][] = 'EXT:hgon_template/Resources/Private/Language/locallang_rkwauthors_db.xlf';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:rkw_basics/Resources/Private/Language/locallang_db.xlf'][] = 'EXT:hgon_template/Resources/Private/Language/locallang_rkwbasics_db.xlf';



//=================================================================
// Add Flexform
//=================================================================
$extensionName = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY));

$pluginName = strtolower('JournalHighlight');
$pluginSignature = $extensionName.'_'.$pluginName;
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/JournalHighlight.xml');

$pluginName = strtolower('PageHighlight');
$pluginSignature = $extensionName.'_'.$pluginName;
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/PageHighlight.xml');

$pluginName = strtolower('RandomAuthor');
$pluginSignature = $extensionName.'_'.$pluginName;
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/RandomAuthor.xml');

$pluginName = strtolower('ProjectTeaser');
$pluginSignature = $extensionName.'_'.$pluginName;
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/ProjectTeaser.xml');

$pluginName = strtolower('PageSlider');
$pluginSignature = $extensionName.'_'.$pluginName;
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/PageSlider.xml');

$pluginName = strtolower('ProjectPartner');
$pluginSignature = $extensionName.'_'.$pluginName;
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/ProjectPartner.xml');

$pluginName = strtolower('AuthorList');
$pluginSignature = $extensionName.'_'.$pluginName;
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/AuthorList.xml');

/*
 * Moved to HGON Donation
$pluginName = strtolower('SupportOptions');
$pluginSignature = $extensionName.'_'.$pluginName;
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/SupportOptions.xml');

$pluginName = strtolower('SupportOptionsLight');
$pluginSignature = $extensionName.'_'.$pluginName;
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/SupportOptionsLight.xml');
*/

$pluginName = strtolower('SixReasons');
$pluginSignature = $extensionName.'_'.$pluginName;
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/SixReasons.xml');
