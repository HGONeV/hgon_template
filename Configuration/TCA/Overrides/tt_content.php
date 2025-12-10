<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function (string $extKey) {

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
            $extKey,
            'JournalHighlight',
            'HGON: Journal Highlights'
        );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extKey,
            'Journal',
            'HGON: Journal'
        );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extKey,
            'Header',
            'HGON: News Header'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extKey,
            'Sidebar',
            'HGON: News Sidebar'
        );



        // ***************
        // Article
        // ***************

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extKey,
            'ShowArticleFromPages',
            'HGON: Bestellung (Artikel)'
        );



        //=================================================================
        // Add Flexform
        //=================================================================
        $extensionName = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($extKey));

        $pluginName = strtolower('JournalHighlight');
        $pluginSignature = $extensionName.'_'.$pluginName;
        $TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
        $TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$extKey . '/Configuration/FlexForms/JournalHighlight.xml');

        $pluginName = strtolower('PageHighlight');
        $pluginSignature = $extensionName.'_'.$pluginName;
        $TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
        $TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$extKey . '/Configuration/FlexForms/PageHighlight.xml');

        $pluginName = strtolower('RandomAuthor');
        $pluginSignature = $extensionName.'_'.$pluginName;
        $TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
        $TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$extKey . '/Configuration/FlexForms/RandomAuthor.xml');

        $pluginName = strtolower('ProjectTeaser');
        $pluginSignature = $extensionName.'_'.$pluginName;
        $TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
        $TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$extKey . '/Configuration/FlexForms/ProjectTeaser.xml');

        $pluginName = strtolower('PageSlider');
        $pluginSignature = $extensionName.'_'.$pluginName;
        $TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
        $TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$extKey . '/Configuration/FlexForms/PageSlider.xml');

        $pluginName = strtolower('ProjectPartner');
        $pluginSignature = $extensionName.'_'.$pluginName;
        $TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
        $TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$extKey . '/Configuration/FlexForms/ProjectPartner.xml');

        $pluginName = strtolower('AuthorList');
        $pluginSignature = $extensionName.'_'.$pluginName;
        $TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
        $TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$extKey . '/Configuration/FlexForms/AuthorList.xml');

        $pluginName = strtolower('SixReasons');
        $pluginSignature = $extensionName.'_'.$pluginName;
        $TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
        $TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$extKey . '/Configuration/FlexForms/SixReasons.xml');


    },
    'hgon_template'
);

