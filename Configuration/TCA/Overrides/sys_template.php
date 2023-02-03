<?php
defined('TYPO3_MODE') || die('Access denied.');

//=================================================================
// Add TypoScript
//=================================================================
$extKey = 'hgon_template';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, 'Configuration/TypoScript', 'HGON Template');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, 'Themes/_Websites/LibellenHessen/Configuration/TypoScript', 'HGON Template - Theme: Libellen');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, 'Themes/_Websites/HeuschreckenHessen/Configuration/TypoScript', 'HGON Template - Theme: Heuschrecken');


