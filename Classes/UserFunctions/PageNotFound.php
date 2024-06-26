<?php

namespace HGON\HgonTemplate\UserFunctions;

use TYPO3\CMS\Core\Messaging\ErrorpageMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use \RKW\RkwBasics\Helper\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class PageNotFound
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwBasics
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PageNotFound extends \RKW\RkwBasics\UserFunctions\PageNotFound
{

    /**
     * Redirect to new page if old one exists, otherwise redirect to default not-found-page
     *
     * @param array $params
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $typoScriptFrontendController
     * @return string
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    function pageNotFound($params = array(), $typoScriptFrontendController = null)
    {
        // clean up path
        $path = $params['currentUrl'];
        if (strpos($path, '/') === 0) {
            $path = substr($path, 1);
        }
        if (strrpos($path, '/') === strlen($path) - 1) {
            $path = substr($path, 0, strlen($path) - 1);
        }

        // host / domain
        $host = GeneralUtility::getIndpEnv('HTTP_HOST');
        if ($_GET['requestHost']) {

            // cut of this special param from path and set host to param value
            $path = preg_replace('/(\?|&)requestHost=[a-z0-9\.\-_]+/i', '', $path);
            $host = $_GET['requestHost'];
        }

        // cleanup
        $path = preg_replace('/[^a-z0-9\-_\.\/]/i', '', $path);
        $host = preg_replace('/[^a-z0-9\-_\.]/i', '', $host);

        // load FE and get configuration
        $this->initTSFE();
        $configuration = $this->getConfiguration();
        $settings = $this->getSettings();


        // further stuff
        $protocol = 'http://';
        if (
            ($_SERVER['HTTPS'])
            || ($_SERVER['SERVER_PORT'] == '443')
        ) {
            $protocol = 'https://';
        }

        $domain = '';
        if ($configuration['defaultDomain']) {
            $domain = $protocol . $configuration['defaultDomain'];
        }

        $languageAddition = '';
        if ($this->getLanguageKey()) {
            $languageAddition = '&L=' . $this->getLanguageKey();
        }

        try {
            // check if there is a redirect page
            if ($redirectPid = $this->getRedirectPid($path, $host)) {

                /** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj */
                $cObj = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
                $link = $cObj->typolink_URL(
                    array(
                        'parameter'        => intval($redirectPid),
                        'additionalParams' => $languageAddition,
                        'forceAbsoluteUrl' => 1,
                    )
                );

                // log what we are doing
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Redirecting "%s" to "%s".', $host . '/' . $path, $link));

                // redirect and exit
                HttpUtility::redirect($link, HttpUtility::HTTP_STATUS_301);

                // check if we were clever enough to set a fallback pid
            } else {

                if ($configuration['fallbackPid']) {

                    // set up context if proxy is used
                    $aContext = array();
                    if ($settings['proxy']) {

                        $aContext = array(
                            'http' => array(
                                'proxy'           => $settings['proxy'],
                                'request_fulluri' => true,
                            ),
                        );

                        if ($settings['proxyUsername']) {
                            $auth = base64_encode($settings['proxyUsername'] . ':' . $settings['proxyPassword']);
                            $aContext['http']['header'] = 'Proxy-Authorization: Basic ' . $auth;
                        }
                    }

                    // Code 404 and exit
                    $cxContext = stream_context_create($aContext);

                    // check if allow_url_fopen is activated
                    $url = $domain . '/index.php?id=' . intval($configuration['fallbackPid']) . $languageAddition . '&originalUrl=' . urlencode($_SERVER['REQUEST_URI']);
                    if (ini_get('allow_url_fopen')) {
                        $content = file_get_contents($url, false, $cxContext);

                    } else {
                        curl_setopt($ch=curl_init(), CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        $content = curl_exec($ch);
                        curl_close($ch);
                    }

                    if ($content) {

                        // log what we are doing
                        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Showing fallback not-found-page for URL "%s".', $host . '/' . $path));

                        HttpUtility::setResponseCode(HttpUtility::HTTP_STATUS_404);
                        echo $content;
                        die();
                        //===
                    }
                }
            }
        } catch (\Exception $e) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('An error occurred while trying to catch the page-not-found-page for URL "%s". Please check the configuration. Error: %s', $host . '/' . $path, $e->getMessage()));
        }

        // log what we are doing
        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Showing default not-found-page for URL "%s".', $host . '/' . $path));

        // fallback of fallback
        $title = 'Page Not Found';
        $message = $params['reasonText'] ? 'Reason: ' . htmlspecialchars($params['reasonText']) : 'Page cannot be found.';
        $messagePage = GeneralUtility::makeInstance(ErrorpageMessage::class, $message, $title);
        $messagePage->output();
        die();
        //===

    }


    /**
     * Returns the language key
     *
     * @return integer
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    private function getLanguageKey()
    {

        // check if there is a language-param in the query string
        $languageKey = 0;
        $pregMatch = array();
        if (preg_match('/L=([0-9]+)/', GeneralUtility::getIndpEnv('QUERY_STRING'), $pregMatch)) {
            if ($pregMatch[1]) {
                $languageKey = intval($pregMatch[1]);
            }
        }


        // check if we can get the language via realUrl
        if (!$languageKey) {

            if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('realurl')) {

                // extract the language name e.g. "de" or "en"
                $pregMatch = array();
                $languageName = null;

                preg_match("/^\/([a-zA-Z]{2})\/(.*)/", GeneralUtility::getIndpEnv('REQUEST_URI'), $pregMatch);
                if ($pregMatch[1]) {
                    $languageName = $pregMatch[1];
                }

                if ($languageName) {

                    // get config for realUrl
                    $realUrlConf = $this->getConfiguration('realurl');

                    // find language key via realUrl
                    foreach ($realUrlConf['preVars'] as $key => $val) {
                        if (
                            (isset($realUrlConf['preVars'][$key]['GETvar']))
                            && ($realUrlConf['preVars'][$key]['GETvar'] == "L")
                        ) {

                            if (
                                is_array($realUrlConf['preVars'][$key]['valueMap'])
                                && array_key_exists($languageName, $realUrlConf['preVars'][$key]['valueMap'])
                            ) {
                                $languageKey = intval($realUrlConf['preVars'][$key]['valueMap'][$languageName]);
                            } elseif ($realUrlConf['preVars'][$key]['valueDefault']) {
                                $languageKey = intval($realUrlConf['preVars'][$key]['valueDefault']);
                            }
                        }
                    }
                }
            }
        }

        return $languageKey;
        //===
    }


    /**
     * Returns the redirect url based on the old page-names
     *
     * @param string $path
     * @param string $domain
     * @return string
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    private function getRedirectPid($path, $domain)
    {

        // search for a matching page
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
            'pages.uid',
            'pages',
            '(
            tx_rkwbasics_old_link = "' . addslashes($path) . '"
            OR tx_rkwbasics_old_link = "' . addslashes($path . '/') . '"
            )
             AND (
                tx_rkwbasics_old_domain = "' . addslashes($domain) . '"
                OR  tx_rkwbasics_old_domain = "' . addslashes($domain . '/') . '"
                OR  tx_rkwbasics_old_domain = "' . addslashes('http://' . $domain) . '"
                OR  tx_rkwbasics_old_domain = "' . addslashes('http://' . $domain . '/') . '"
            )' .
            \RKW\RkwBasics\Helper\QueryTypo3::getWhereClauseForEnableFields('pages') .
            \RKW\RkwBasics\Helper\QueryTypo3::getWhereClauseForVersioning('pages'),
            $groupBy = '',
            $orderBy = ''
        );

        if ($result['uid']) {
            return intval($result['uid']);
        }

        //===

        return null;
        //===
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    private function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTyposcriptConfiguration('RkwBasics', $which);
        //===
    }


    /**
     * Returns configuration
     *
     * @param string $key
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    private function getConfiguration($key = 'rkw_basics')
    {

        // get it from configuration
        $host = GeneralUtility::getIndpEnv('HTTP_HOST');
        $config = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$key];

        // get settings and merge
        $settings = $this->getSettings();
        if (
            (is_array($settings))
            && (is_array($settings['pageNotFoundConfig']))
        ) {
            $config = array_merge($settings['pageNotFoundConfig'], $config);
        }

        if ($config[$host]) {
            return $config[$host];
            //===
        }


        return $config['_DEFAULT'];
        //===

    }

    /**
     * init frontend to render frontend links in task
     *
     * @param integer $id
     * @param integer $typeNum
     * @return void
     */
    private function initTSFE($id = 0, $typeNum = 0)
    {

        if (!$id) {
            $id = 1;
            if ($GLOBALS['TSFE']->id) {
                $id = $GLOBALS['TSFE']->id;
            }
        }
        if (!is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = new \TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
            $GLOBALS['TT']->start();
        }
        $GLOBALS['TSFE'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'], $id, $typeNum);
        $GLOBALS['TSFE']->connectToDB();
        $GLOBALS['TSFE']->initFEuser();
        $GLOBALS['TSFE']->determineId();
        $GLOBALS['TSFE']->initTemplate();
        $GLOBALS['TSFE']->getConfigArray();

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('realurl')) {
            $rootline = \TYPO3\CMS\Backend\Utility\BackendUtility::BEgetRootLine($id);
            $host = \TYPO3\CMS\Backend\Utility\BackendUtility::firstDomainRecord($rootline);
            $_SERVER['HTTP_HOST'] = $host;
            $GLOBALS['TSFE']->config['config']['absRefPrefix'] = $host;
        }

    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    private function getLogger()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
        //===
    }


}