<?php

namespace HGON\HgonTemplate\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
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
 * Class EventRepository
 * The repository for events
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EventRepository extends \RKW\RkwEvents\Domain\Repository\EventRepository
{


    /**
     * Return not finished events
     * For the FIRST result page (just with simple limit)
     *
     * @param int $limit
     * @param array $settings
     * @param boolean $isWorkGroupEvent sets the event type (standard or workgroup)
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findNotFinishedOrderAsc($limit, $settings = array(), $isWorkGroupEvent = false)
    {

        $query = $this->createQuery();

        $constraints = array(
            $query->greaterThanOrEqual('start', time()),
            $query->logicalNot($query->equals('title', '')),
        );

        if (
            (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects'))
            && ($settings['projectUids'])
            && ($projectUids = GeneralUtility::trimExplode(',', $settings['projectUids'], true))
        ) {
            $constraints[] = $query->in('project', $projectUids);
        }

        if ($isWorkGroupEvent) {
            // is workgroup-meeting-event
            $constraints[] = $query->greaterThan('txHgonWorkgroupWgevent', 0);
        } else {
            // is standard-event
            // Fix for problem: Events without no workGroup does not show. Issue: They either no wgevent nor stdevent
            // Solution: Show also if either stdEvent, or either no stdevent AND no wgevent
            $constraints[] =
                $query->logicalOr(
                    $query->greaterThan('txHgonWorkgroupStdevent', 0),
                    $query->logicalAnd(
                        $query->equals('txHgonWorkgroupStdevent', 0),
                        $query->equals('txHgonWorkgroupWgevent', 0)
                    )
                );
        }

        return $query->matching(
            $query->logicalAnd($constraints)
        )
            ->setOrderings(
                array(
                    'start' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
                )
            )
            ->setLimit($limit)
            ->execute();
    }


    /**
     * Return finished events
     * For the FIRST result page (just with simple limit)
     *
     * @param int $limit
     * @param array $settings
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findFinishedOrderAsc($limit, $settings = array())
    {

        $query = $this->createQuery();
        $constraints = array(
            $query->lessThan('start', time()),
            $query->logicalNot($query->equals('title', '')),
        );

        if (
            (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects'))
            && ($settings['projectUids'])
            && ($projectUids = GeneralUtility::trimExplode(',', $settings['projectUids'], true))
        ) {
            $constraints[] = $query->in('project', $projectUids);
        }

        // is standard-event
        $constraints[] = $query->logicalOr(
            $query->greaterThan('txHgonWorkgroupStdevent', 0),
            $query->logicalAnd(
                $query->equals('txHgonWorkgroupStdevent', 0),
                $query->equals('txHgonWorkgroupWgevent', 0)
            )
        );

        return $query->matching(
            $query->logicalAnd($constraints)
        )
            ->setOrderings(
                array(
                    'start' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                )
            )
            ->setLimit($limit)
            ->execute();
    }



    /**
     * findByFilterOptions
     *
     * @param array $filter
     * @param int $limit
     * @param int $page
     * @param boolean $archive
     * @param boolean $isWorkGroupEvent sets the event type (standard or workgroup)
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByFilterOptions($filter, $limit, $page, $archive = false, $isWorkGroupEvent = false)
    {
        // results between 'from' and 'till' (with additional proof item to check, if there are more results -> +1)
        $offset = $page * $limit;
        $limit = $limit + 1;

        // if we are on a page > 1, we also fetch none item twice
        // we need this to figure out which date was the last for grouping!
        if ($page > 0) {
            // workgroup list is counting another way
            if (!$isWorkGroupEvent) {
                $offset--;
                $limit++;
            }

        }

        $query = $this->createQuery();
        $constraints = array();

        // set initial start time (normally overwritten below)
        $constraints[] = $query->greaterThanOrEqual('start', time());

        // additional filter options
        if ($filter['time']) {
            $constraints[] = $query->greaterThanOrEqual('start', intval($filter['time']));
        }
        if ($filter['documentType']) {
            $constraints[] = $query->equals('documentType', intval($filter['documentType']));
        }


        if ($isWorkGroupEvent) {
            // is workgroup-meeting-event
            $constraints[] = $query->greaterThan('txHgonWorkgroupWgevent', 0);
            if ($filter['workGroup']) {
                $constraints[] = $query->contains('txHgonWorkgroupWgevent', intval($filter['workGroup']));
            }
        } else {
            // is standard-event
            $constraints[] = $query->logicalOr(
                $query->greaterThan('txHgonWorkgroupStdevent', 0),
                $query->logicalAnd(
                    $query->equals('txHgonWorkgroupStdevent', 0),
                    $query->equals('txHgonWorkgroupWgevent', 0)
                )
            );
            if ($filter['workGroup']) {
                $constraints[] = $query->contains('txHgonWorkgroupStdevent', intval($filter['workGroup']));
            }
        }

        // NOW: construct final query!
        if ($constraints) {
            $query->matching($query->logicalAnd($constraints));
        }

        $query->setOffset($offset);
        $query->setLimit($limit);

        // 1. Sort by end-date
        $query->setOrderings(
            array(
                'start' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
            )
        );

        // Hint: if no query is added, this dataset is equal to findAll() with sort & date restriction
        $result = $query->execute();

        return $result;
        //===
    }



    /**
     * Find a count of articles until a set max date
     *
     * @param integer
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     */
    public function findByMaxDate($maxTimestamp, $count = 1)
    {
        $query = $this->createQuery();

        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->lessThanOrEqual('crdate', $maxTimestamp)
        );

        $query->setLimit($count);

        return $query->execute();
        //===
    }



    /**
     * Returns upcoming events (no work group events!)
     *
     * @param int $limit
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findUpcoming($limit = 3)
    {
        $query = $this->createQuery();

        // set this flag for newsletter "GetEventRecordsViewHelper"
        $query->getQuerySettings()->setRespectStoragePage(false);

        return $query->matching(
            $query->logicalAnd(
                $query->logicalAnd(
                    $query->logicalAnd(
                        $query->logicalAnd(
                            $query->greaterThanOrEqual('start', time()),
                            $query->equals('txHgonWorkgroupStdevent', 1)
                        ),
                        $query->greaterThan('end', time())
                    ),
                    $query->logicalOr(
                        $query->equals('onlineEvent', 0),
                        $query->logicalAnd(
                            $query->equals('onlineEvent', 1),
                            $query->greaterThan('onlineEventAccessLink', 0)
                        )
                    )
                )
            ),
            $query->setLimit(intval($limit))
        )->execute();
        //===
    }
}