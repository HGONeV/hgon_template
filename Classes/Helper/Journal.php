<?php
namespace HGON\HgonTemplate\Helper;

/***
 *
 * This file is part of the "HGON Template" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2018 Maximilian Fäßler <maximilian@faesslerweb.de>, Fäßler Web UG
 *
 ***/
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use HGON\HgonTemplate\Domain\Model\SysCategory;

/**
 * Journal
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Journal implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * pagesRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\PagesRepository
     * @inject
     */
    protected $pagesRepository = null;

    /**
     * authorsRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\AuthorsRepository
     * @inject
     */
    protected $authorsRepository = null;

    /**
     * projectsRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\ProjectsRepository
     * @inject

    protected $projectsRepository = null;*/

    /**
     * sysCategoryRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\SysCategoryRepository
     * @inject
     */
    protected $sysCategoryRepository = null;

    /**
     * eventRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\EventRepository
     * @inject
     */
    protected $eventRepository = null;

    /**
     * getPagesList
     * Duplicates a part of the journalAction
     *
     * @param array $settings
     * @param int $pageNumber
     * @param \HGON\HgonTemplate\Domain\Model\SysCategory $sysCategory
     * @return array
     */
    public function getPagesList($settings, $pageNumber, SysCategory $sysCategory = null)
    {
        $returnArray = [];

        if ($sysCategory) {
            $journalParentCategory = $sysCategory;
        } else {
            $journalParentCategory = $this->sysCategoryRepository->findByIdentifier($settings['journal']['parentCategoryUid']);
        }

        // category list (find by "journal"-sysCategory, or given sysCategory)
        $returnArray['sysCategoryList'] = $this->sysCategoryRepository->findOneWithAllRecursiveChildren($journalParentCategory, null, true);

        // pages list
        $pagesWithCategoryList = $this->pagesRepository->findBySysCategory($returnArray['sysCategoryList']);
        if ($pagesWithCategoryList->count()) {
            $returnArray['journalRowList'] = $this->pagesRepository->findTreeByParentPages($pagesWithCategoryList, true, $pageNumber, intval($settings['journal']['itemsPerPage']));
            //===
        }

        return $returnArray;
        //===
    }

    /**
     * createResultSet
     *
     * @param array $settings
     * @param int $pageNumber
     * @param \HGON\HgonTemplate\Domain\Model\SysCategory $sysCategory
     * @return array
     */
    public function createResultSet($settings, $pageNumber, SysCategory $sysCategory = null)
    {

        // @toDo: Was könnte bei ExcludePages noch eingetragen werden?
        $excludePages = [];
        if ($settings['journal']['excludePidList']) {
            $excludePages = array_merge($excludePages, GeneralUtility::trimExplode(',', $settings['excludePidList']));
        }

        // sysCategory is set, if user is filtering
        if ($sysCategory) {
            $journalParentCategory = $sysCategory;
        } else {
            $journalParentCategory = $this->sysCategoryRepository->findByIdentifier($settings['journal']['parentCategoryUid']);
        }

        // category list (find by "journal"-sysCategory, or given sysCategory)
        $sysCategoryList = $this->sysCategoryRepository->findOneWithAllRecursiveChildren($journalParentCategory, null, true);

        // pages list
        $pagesWithCategoryList = $this->pagesRepository->findBySysCategory($sysCategoryList);
        if ($pagesWithCategoryList->count()) {
            $pagesList = $this->pagesRepository->findTreeByParentPages($pagesWithCategoryList, true, $pageNumber, 9999);
            // $this->view->assign('pagesList', $pagesList);
//DebuggerUtility::var_dump($pagesList); exit;
            // split in four parts (2 x 3/3 width; 1 x 2/3; 1x 1/2)
            $pagesListArray = $pagesList->toArray();
            // create fluid rows
            $itemRowList = [];
            $usedItemsCount = 0;
            $row = 1;
            do {
                $minItems = 1;
                $maxItems = ($pagesList->count() - $usedItemsCount) >= 3 ? 3 : ($pagesList->count() - $usedItemsCount);
                $rowItems = mt_rand($minItems, $maxItems);
                $pagesInRow = array_slice($pagesListArray, $usedItemsCount, $rowItems);
                // increment for loop
                $usedItemsCount += $rowItems;
                // fill rows
                // -> By three items add nothing (row is filled with 3x c4)
                if ($rowItems == 3) {
                    foreach ($pagesInRow as $pages) {
                        $itemRowList[$row]['FlexItemC4'][] = $pages;
                    }
                } elseif ($rowItems == 2) {
                    // -> By two items make random, a) to use 2x c6 (standalone), b) or 2x c4 with additional c4
                    $boolRand = mt_rand(0, 1);
                    if ($boolRand) {
                        foreach ($pagesInRow as $pages) {
                            $itemRowList[$row]['FlexItemC6'][] = $pages;
                        }
                    } else {
                        foreach ($pagesInRow as $pages) {
                            $itemRowList[$row]['FlexItemC4'][] = $pages;
                        }
                        $itemRowList[$row]['FlexItemC4'][] = $this->addFlexItemC4();
                    }
                } elseif ($rowItems == 1) {
                    // -> By one item make random, use 1x c4; or c6; or c8 - and fill with further items
                    $rand = mt_rand(1, 3);
                    if ($rand == 1) {
                        foreach ($pagesInRow as $pages) {
                            $itemRowList[$row]['FlexItemC4'][] = $pages;
                        }
                        // Add 2x c4 or 1x c8
                        $boolRand = mt_rand(0, 1);
                        if ($boolRand) {
                            // add 2x c4
                            $itemRowList[$row]['FlexItemC4'][] = $this->addFlexItemC4();
                            $itemRowList[$row]['FlexItemC4'][] = $this->addFlexItemC4();
                        } else {
                            $itemRowList[$row]['FlexItemC8'][] = $this->addFlexItemC8();
                        }

                    } elseif ($rand == 2) {
                        foreach ($pagesInRow as $pages) {
                            $itemRowList[$row]['FlexItemC6'][] = $pages;
                        }

                        $itemRowList[$row]['FlexItemC6'][] = $this->addFlexItemC6();

                    } elseif ($rand == 3) {
                        foreach ($pagesInRow as $pages) {
                            $itemRowList[$row]['FlexItemC8'][] = $pages;
                        }

                        $itemRowList[$row]['FlexItemC4'][] = $this->addFlexItemC4();

                    }
                } else {
                    // if we don't get 4 rows: Fill it
                    // Add c12
                    $itemRowList[$row]['FlexItemC12'][] = $this->addFlexItemC12();
                }

                $row++;
            } while($usedItemsCount < $pagesList->count() || $row <= 4);

            // @toDo: Additional shuffle Row-array to make it more fluid?

        //    DebuggerUtility::var_dump($itemRowList);

            return $itemRowList;
            //===

            /*
            $templateDataArray['pagesListRow1'] = array_slice($pagesListArray, 0, 3);
            $templateDataArray['pagesListRow2'] = array_slice($pagesListArray, 3, 1);
            $templateDataArray['pagesListRow3'] = array_slice($pagesListArray, 4, 1);
            $templateDataArray['pagesListRow4'] = array_slice($pagesListArray, 5, 3);
            */
        }

    }


    /**
     * addFlexItemC4
     * Can be an event, author, or a tweet (social media)
     *
     * @return mixed
     */
    protected function addFlexItemC4()
    {
        $rand = mt_rand(1, 3);
        if ($rand === 1) {
            // get event
            $eventList = $this->eventRepository->findAll()->toArray();
            return $eventList[rand(0, count($eventList) - 1)];
            //===
        } elseif ($rand == 2) {
            // get author
            $authorList = $this->authorsRepository->findAll()->toArray();
            return $authorList[rand(0, count($authorList) - 1)];
            //===
        }

        // Else: return a tweet
        $tweet = new \stdClass();
        $tweet->description = "This is not a tweet yet";
        return $tweet;
    }



    /**
     * addFlexItemC6
     * Can be an event, a news, or an author
     *
     * @return mixed
     */
    protected function addFlexItemC6()
    {
        // @toDo: news does not exists yet
        // $rand = mt_rand(1, 3);
        $rand = mt_rand(2, 3);
        if ($rand === 1) {
            // get news
            $eventList = $this->eventRepository->findAll()->toArray();
            return $eventList[rand(0, count($eventList) - 1)];
            //===
        } elseif ($rand == 2) {
            // get author
            $authorList = $this->authorsRepository->findAll()->toArray();
            return $authorList[rand(0, count($authorList) - 1)];
            //===
        }

        // get event
        $eventList = $this->eventRepository->findAll()->toArray();
        return $eventList[rand(0, count($eventList) - 1)];
        //===
    }



    /**
     * addFlexItemC8
     * Can be an event, or an author
     *
     * @return mixed
     */
    protected function addFlexItemC8()
    {
        // @toDo: "DidYouKnow?"

        /* Autoren sehen optisch in C8 nicht besonders gut aus
        $rand = mt_rand(1, 2);
        if ($rand) {
            // get author
            $authorList = $this->authorsRepository->findAll()->toArray();
            return $authorList[rand(0, count($authorList) - 1)];
            //===
        }
        */

        // @toDo: Return two events?
        // get event
        $eventList = $this->eventRepository->findAll()->toArray();
        return $eventList[rand(0, count($eventList) - 1)];
        //===
    }



    /**
     * addFlexItemC12
     * Can be an event, or an author
     *
     * @return mixed
     */
    protected function addFlexItemC12()
    {
        // @toDo: "DidYouKnow?"
        /*
        $rand = mt_rand(1, 2);
        if ($rand) {
            // get author
            $authorList = $this->authorsRepository->findAll()->toArray();
            return $authorList[rand(0, count($authorList) - 1)];
            //===
        }
        */

        // Return three events
        // get event
        $eventList = $this->eventRepository->findAll()->toArray();
        return array_slice($eventList, 0, 3);
        //return $eventList[rand(0, count($eventList) - 1)];
        //===
    }


}