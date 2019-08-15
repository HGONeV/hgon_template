<?php

namespace HGON\HgonTemplate\Controller;

use RKW\RkwEvents\Helper\DivUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \RKW\RkwBasics\Helper\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
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
 * EventAjaxController
 */
class EventAjaxController extends \RKW\RkwEvents\Controller\AjaxController
{
    /**
     * eventRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\EventRepository
     * @inject
     */
    protected $eventRepository = null;


    /**
     * filterAction
     *
     * @param array $filter
     * @param integer $page
     *
     * @return void

    public function filterAction($filter = array(), $page = 0)
    {
        // we just have to override the eventRepository (to use different filter options)
        parent::filterAction($filter, $page);


        // @toDo: The parent::filterAction above have to be set to the end.
        // -> But problem: The "print (string)$jsonHelper;" line throws an error, without an "exit;"
        // -> But on the one hand we want to renew the WorkGroup-Events.

        // only on filtering
        if ($filter) {
            // workgroups (without pagination)
            $hgonWorkgroupSettings = self::getSettings('HgonWorkgroup', ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

            // search only for workgroup meeting - is differ to the select options of a special workgroup below
            $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\QuerySettingsInterface');
            $querySettings->setStoragePageIds([$hgonWorkgroupSettings['persistence']['storagePid']]);
            $this->eventRepository->setDefaultQuerySettings($querySettings);
            $workGroupList = $this->eventRepository->findByFilterOptions($filter, 9999, 0, false);

            // @toDo: Set query result
            // get JSON helper
            /** @var \RKW\RkwBasics\Helper\Json $jsonHelper
            $jsonHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwBasics\\Helper\\Json');
            $replacements['workGroupList'] = $workGroupList;
            $jsonHelper->setHtml(
                'tx-rkwevents-result-section-workgroup',
                $replacements,
                'replace',
                'Ajax/List/MoreWorkGroup.html'
            );
            print (string)$jsonHelper;

            // reset original storagePid (for following parent call which handles standard events)
            $rkwEventsSettings = self::getSettings('RkwEvents', ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
            $querySettings->setStoragePageIds([$rkwEventsSettings['persistence']['storagePid']]);
            $this->eventRepository->setDefaultQuerySettings($querySettings);
        }


    }
     */

    /**
     * filterAction
     *
     * @param array $filter
     * @param integer $page
     * @param bool $archive
     * @param boolean $isWorkGroupEvent sets the event type (uses in more-button)
     * @return void
    */
    public function filterAction($filter = array(), $page = 0, $archive = false, $isWorkGroupEvent = false)
    {
        // @toDo: The parent::filterAction above have to be set to the end.
        // -> But problem: The "print (string)$jsonHelper;" line throws an error, without an "exit;"
        // -> But on the one hand we want to renew the WorkGroup-Events.
        $workGroupEventList = [];
        // only on filtering
        if (
            $filter
            || ($isWorkGroupEvent && $page > 0)
        ) {
            // workgroups (without pagination)
            $hgonWorkgroupSettings = self::getSettings('HgonWorkgroup', ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

            // search only for workgroup meeting - is differ to the select options of a special workgroup below
            $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\QuerySettingsInterface');
            $querySettings->setStoragePageIds([$hgonWorkgroupSettings['persistence']['storagePid']]);
            $this->eventRepository->setDefaultQuerySettings($querySettings);
            $listItemsPerView = intval($this->settings['itemsPerPage']) ? intval($this->settings['itemsPerPage']) : 10;
            $workGroupEventList = $this->eventRepository->findByFilterOptions($filter, $listItemsPerView, intval($page), false, true);

            // if $isWorkGroupEvent is set: This is the "show more"-Button for workGroupEvents. Just replace workgroupEvent and go out
            // (otherwise, if filter request: The script also uses the part below and sets the workGroupEventList to the view)
            if (
                $isWorkGroupEvent
                && $page > 0
            ) {
                // get JSON helper
                /** @var \RKW\RkwBasics\Helper\Json $jsonHelper */
                $jsonHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwBasics\\Helper\\Json');
                $replacements['workGroupEventList'] = $workGroupEventList;
                $jsonHelper->setHtml(
                    'tx-rkwevents-grid-section-workgroup',
                    $replacements,
                    'replace',
                    'Ajax/List/More.html'
                );
                print (string)$jsonHelper;
                exit;
            }


            // reset original storagePid (for following parent call which handles standard events)
            //$rkwEventsSettings = self::getSettings('RkwEvents', ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
            //$querySettings->setStoragePageIds([$rkwEventsSettings['persistence']['storagePid']]);
            //$this->eventRepository->setDefaultQuerySettings($querySettings);
        }


        // we just have to override the eventRepository (to use different filter options)
        // @toDo: does not work because of multiple print of the jsonHelper
        //parent::filterAction($filter, $page);


        //#####################################################################################
        // code of RkwEvents->AjaxController->listAction (with small adjustments)
        //#####################################################################################

        // 1. filter the filterArray ;-)
        foreach ($filter as $key => $value) {
            $filter[$key] = filter_var($value, FILTER_SANITIZE_STRING);
        }

        // 2. get event list
        $listItemsPerView = intval($this->settings['itemsPerPage']) ? intval($this->settings['itemsPerPage']) : 10;
        $queryResult = $this->eventRepository->findByFilterOptions($filter, $listItemsPerView, intval($page), boolval($archive));

        // 3. proof if we have further results (query with listItemsPerQuery + 1)
        $lastItem = null;
        $eventList = DivUtility::prepareResultsList($queryResult, $listItemsPerView, intval($page), $lastItem);

        // 4. Check if we need to display a more-link
        $showMoreLink = count($eventList) < count($queryResult) ? true : false;
        if ($page > 0) {
            $showMoreLink = count($eventList) < (count($queryResult) - 1) ? true : false;
        }


        // 5. sort event list (group by month) - only if no distance search is performed
        $sortedEventList = array();
        if (
            ($page > 0)
            && (!$filter['address'])
        ) {
            $sortedEventList = DivUtility::groupEventsByMonthMore($eventList, $lastItem);

        } else {
            if (!$filter['address']) {
                $sortedEventList = DivUtility::groupEventsByMonth($eventList);
            }
        }

        // get new list
        $replacements = array(
            'ajaxTypeNum'  => intval($this->settings['ajaxTypeNum']),
            'showPid'      => intval($this->settings['showPid']),
            'pageMore'     => $page + 1,
            'showMoreLink' => $showMoreLink,
            'filter'       => $filter,
            'noGrouping'   => ($filter['address'] ? true : false),
        );

        // get JSON helper
        /** @var \RKW\RkwBasics\Helper\Json $jsonHelper */
        $jsonHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwBasics\\Helper\\Json');
        if ($page > 0) {

            // if a distance search is performed we do not group by month
            if ($filter['address']) {

                $replacements['sortedEventList'] = $eventList;
                $replacements['geosearch'] = true;
                $replacements['noGrouping'] = true;

                $jsonHelper->setHtml(
                    'tx-rkwevents-grid-section',
                    $replacements,
                    'append',
                    'Ajax/List/More.html'
                );

            } else {

                // set append list
                if ($sortedEventList['append']) {
                    $replacements['sortedEventList'] = $sortedEventList['append'];
                }

                $jsonHelper->setHtml(
                    'tx-rkwevents-grid-section',
                    $replacements,
                    'append',
                    'Ajax/List/More.html'
                );


                // set insert list
                if (
                    ($lastItem instanceof \RKW\RkwEvents\Domain\Model\Event)
                    && ($sortedEventList['insert'])
                ) {

                    $startDateLastItem = new \DateTime(date('d-m-Y', $lastItem->getStart()));
                    $replacements['sortedEventList'] = $sortedEventList['insert'];
                    $replacements['noGrouping'] = true;
                    $replacements['showMoreLink'] = false;

                    $jsonHelper->setHtml(
                        'tx-rkwevents-results-' . $startDateLastItem->format("Y") . '-' . $startDateLastItem->format("m"),
                        $replacements,
                        'append',
                        'Ajax/List/More.html'
                    );
                }
            }

        } else {

            // if a distance search is performed we do not group by month
            if ($filter['address']) {

                $replacements['sortedEventList'] = $eventList;
                $replacements['geosearch'] = true;
                $replacements['noGrouping'] = true;
                $jsonHelper->setHtml(
                    'tx-rkwevents-result-section',
                    $replacements,
                    'replace',
                    'Ajax/List/List.html'
                );

            } else {
                if ($workGroupEventList) {
                    $replacements['workGroupEventList'] = $workGroupEventList;
                }
                $replacements['sortedEventList'] = $sortedEventList;
                $jsonHelper->setHtml(
                    'tx-rkwevents-result-section',
                    $replacements,
                    'replace',
                    'Ajax/List/List.html'
                );
            }

        }
        print (string)$jsonHelper;
        exit();
        //===

    }



    /**
     * Returns TYPO3 settings
     *
     * @param string $extension extension name
     * @param string $which Which type of settings will be loaded
     * @return array
     */
    protected static function getSettings($extension = 'Rkwevents', $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTyposcriptConfiguration($extension, $which);
        //===
    }

}
