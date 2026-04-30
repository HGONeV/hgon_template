<?php
defined('TYPO3') or die("Access denied.");

call_user_func(
    function (string $extKey) {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extKey,
            'PageHighlight',
            'HGON: Seiten-Highlight'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extKey,
            'RandomAuthor',
            'HGON: Zufälliger HGON-Autor mit Zitat'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extKey,
            'SiblingPagesOverview',
            'HGON: Generiert Vorschau für Geschwisterseiten'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extKey,
            'ChildrenPagesOverview',
            'HGON: Generiert Vorschau für Unterseiten'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extKey,
            'PageSlider',
            'HGON: Slider Projekte (Seiten)'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extKey,
            'DonationForm',
            'HGON: Spendenformular'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extKey,
            'ContactForm',
            'HGON: Kontaktformular'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extKey,
            'SixReasons',
            'HGON: Sechs Gründe'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extKey,
            'DidYouKnow',
            'HGON: Wussten Sie schon?'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extKey,
            'Maps',
            'HGON: Zeigt HGON auf GoogleMaps'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extKey,
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
        // Add Flexform (CType)
        //=================================================================
        $extensionName = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($extKey));
        $addFlexForm = static function (string $pluginSignature, string $flexFormFile): void {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
                '*',
                $flexFormFile,
                $pluginSignature
            );
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
                'tt_content',
                'pi_flexform',
                $pluginSignature,
                'after:header'
            );
        };

        $pluginName = strtolower('JournalHighlight');
        $pluginSignature = $extensionName.'_'.$pluginName;
        $addFlexForm($pluginSignature, 'FILE:EXT:'.$extKey . '/Configuration/FlexForms/JournalHighlight.xml');

        $pluginName = strtolower('PageHighlight');
        $pluginSignature = $extensionName.'_'.$pluginName;
        $addFlexForm($pluginSignature, 'FILE:EXT:'.$extKey . '/Configuration/FlexForms/PageHighlight.xml');

        $pluginName = strtolower('RandomAuthor');
        $pluginSignature = $extensionName.'_'.$pluginName;
        $addFlexForm($pluginSignature, 'FILE:EXT:'.$extKey . '/Configuration/FlexForms/RandomAuthor.xml');

        $pluginName = strtolower('PageSlider');
        $pluginSignature = $extensionName.'_'.$pluginName;
        $addFlexForm($pluginSignature, 'FILE:EXT:'.$extKey . '/Configuration/FlexForms/PageSlider.xml');

        $pluginName = strtolower('AuthorList');
        $pluginSignature = $extensionName.'_'.$pluginName;
        $addFlexForm($pluginSignature, 'FILE:EXT:'.$extKey . '/Configuration/FlexForms/AuthorList.xml');

        $pluginName = strtolower('SixReasons');
        $pluginSignature = $extensionName.'_'.$pluginName;
        $addFlexForm($pluginSignature, 'FILE:EXT:'.$extKey . '/Configuration/FlexForms/SixReasons.xml');


    },
    'hgon_template'
);
