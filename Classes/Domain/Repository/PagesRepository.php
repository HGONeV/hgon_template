<?php
namespace HGON\HgonTemplate\Domain\Repository;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use RKW\RkwBasics\Helper\QueryTypo3;
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
 * Class PagesRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package RKW_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PagesRepository extends \RKW\RkwBasics\Domain\Repository\PagesRepository
{
    /**
     * Find by PID (and exclude given element)
     *
     * @param \HGON\HgonTemplate\Domain\Model\Pages $pages
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     */
    public function findByPagesExcludeCurrent(\HGON\HgonTemplate\Domain\Model\Pages $pages)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('pid', $pages->getPid()),
                $query->logicalNot(
                    $query->equals('uid', $pages->getUid())
                )
            )
        );

        return $query->execute();
        //===
    }



    /**
     * Find pages with projects
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     */
    public function findAllWithProject()
    {
        $query = $this->createQuery();
        $query->matching(
            $query->greaterThan('txRkwprojectsProject', 0)
        );

        return $query->execute();
        //===
    }



    /**
     * Get pages with certain categories
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage|array $sysCategoryList
     * @param array $excludePages
     * @param integer $pageNumber
     * @param integer $limit
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findBySysCategory($sysCategoryList, $excludePages = array(0 => 1), $pageNumber = 1, $limit = 5)
    {
        // if single item is given, prepare for "in"-Query
        if ($sysCategoryList instanceof \HGON\HgonTemplate\Domain\Model\SysCategory) {
            $sysCategoryList = [$sysCategoryList];
        }

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        // Offset
        $offset = ((intval($pageNumber) - 1) * $limit) + 1;
        if ($pageNumber <= 1) {
            $offset = 0;
        }

        $query->matching(
            $query->logicalAnd(
                $query->in('categories.uid', $sysCategoryList),
                $query->logicalNot(
                    $query->in('uid', $excludePages)
                ),
                $query->equals('doktype', 1)
            ),
            $query->setLimit($limit),
            $query->setOffset($offset)
        );

        return $query->execute();
        //===
    }



    /**
     * Get pages of certain parent pages (used for "journal")
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array $parentPagesList
     * @param boolean $excludeParentPages
     * @param integer $pageNumber
     * @param integer $limit
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findTreeByParentPages($parentPagesList, $excludeParentPages = false, $pageNumber = 1, $limit = 8)
    {
        // 1. get string with all pageUids
        $pagesListArray = [];
        $depth = 999999;
        $queryGenerator = GeneralUtility::makeInstance( 'TYPO3\\CMS\\Core\\Database\\QueryGenerator' );
        foreach ($parentPagesList as $parentPages) {
            $pagesListArray[] = $queryGenerator->getTreeList($parentPages->getUid(), $depth, 0, 1);
        }
        $pagesTreeList = implode(',', $pagesListArray);

        // 2. start query
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        // 3. Offset
        $offset = ((intval($pageNumber) - 1) * $limit) + 1;
        if ($pageNumber <= 1) {
            $offset = 0;
        }

        // 4. Query parts
        $constraints = [];
        $constraints[] = $query->logicalAnd(
            $query->in('uid', explode(',', $pagesTreeList)),
            $query->equals('doktype', 1)
        );

        if ($excludeParentPages) {
            if ($parentPagesList instanceof \HGON\HgonTemplate\Domain\Model\SysCategory) {
                $parentPagesList = [$parentPagesList];
            }
            $constraints[] = $query->logicalNot(
                $query->in('uid', $parentPagesList)
            );
        }

        // 5. Build query
        $query->matching($query->logicalAnd($constraints));

        // Order by lastUpdate
        $query->setOrderings(
            array(
                'lastUpdated' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                'tstamp' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
            )
        );

        $query->setLimit($limit);
        $query->setOffset($offset);

        return $query->execute();
        //===
    }



    /**
     * Find by multiple uids
     *
     * @param string $uidList
     * @return array
     */
    public function findByUidList($uidList)
    {
        $uidArray = GeneralUtility::trimExplode(',', $uidList);

        $resultList = [];

        // to get the flexforms sorting, we'll search for every entry
        foreach ($uidArray as $uid){
            $query = $this->createQuery();
            $query->matching(
                $query->equals('uid', $uid)
            );
            $resultList[] = $query->execute()->getFirst();
        }

        return $resultList;
        //===
    }

}

?>