<?php
namespace HGON\HgonTemplate\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

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
class PagesRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    public function initializeObject(): void
    {
        /** @var Typo3QuerySettings $querySettings */
        $querySettings = $this->createQuery()->getQuerySettings();
        $querySettings->setRespectStoragePage(false);

        // optional, je nach Setup:
        // $querySettings->setRespectSysLanguage(false);

        $this->setDefaultQuerySettings($querySettings);
    }


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
        // 1) Parent UIDs normalisieren
        $parentUids = [];
        foreach ((array)$parentPagesList as $parentPage) {
            if (is_object($parentPage) && method_exists($parentPage, 'getUid')) {
                $parentUids[] = (int)$parentPage->getUid();
            } elseif (is_numeric($parentPage)) {
                $parentUids[] = (int)$parentPage;
            }
        }
        $parentUids = array_values(array_unique(array_filter($parentUids)));

        if ($parentUids === []) {
            return $this->createQuery()->matching($this->createQuery()->equals('uid', 0))->execute();
        }

        // 2) Subtree-Uids sammeln (inkl. Parent-Seiten)
        $allUids = $this->collectDescendantPageUids($parentUids);

        if ($excludeParentPages) {
            $allUids = array_values(array_diff($allUids, $parentUids));
        }

        if ($allUids === []) {
            return $this->createQuery()->matching($this->createQuery()->equals('uid', 0))->execute();
        }

        // 3) Extbase Query
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        // 4) Pagination (0-basiert)
        $pageNumber = max(1, (int)$pageNumber);
        $limit = max(1, (int)$limit);
        $offset = ($pageNumber - 1) * $limit;

        // 5) Constraints
        $constraints = [
            $query->in('uid', $allUids),
            $query->equals('doktype', 1),
        ];

        $query->matching($query->logicalAnd(...$constraints));

        $query->setOrderings([
            'lastUpdated' => QueryInterface::ORDER_DESCENDING,
            'tstamp'      => QueryInterface::ORDER_DESCENDING,
        ]);

        $query->setLimit($limit);
        $query->setOffset($offset);

        return $query->execute();
    }

    /**
     * Liefert alle Descendant-Uids der übergebenen Root-Uids (inkl. Roots).
     * TYPO3-13-kompatibler Ersatz für QueryGenerator->getTreeList().
     */
    private function collectDescendantPageUids(array $rootUids, int $maxDepth = 999999): array
    {
        $rootUids = array_values(array_unique(array_map('intval', $rootUids)));
        $all = $rootUids;
        $current = $rootUids;

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');

        for ($depth = 0; $depth < $maxDepth; $depth++) {
            if ($current === []) {
                break;
            }

            // chunks schützen vor zu langen IN()-Listen
            $children = [];
            foreach (array_chunk($current, 500) as $chunk) {
                $qb = clone $queryBuilder;
                $rows = $qb
                    ->select('uid')
                    ->from('pages')
                    ->where(
                        $qb->expr()->in(
                            'pid',
                            $qb->createNamedParameter($chunk, QueryHelper::PARAM_INT_ARRAY)
                        )
                    )
                    ->executeQuery()
                    ->fetchFirstColumn();

                foreach ($rows as $uid) {
                    $children[] = (int)$uid;
                }
            }

            $children = array_values(array_unique(array_diff($children, $all)));
            if ($children === []) {
                break;
            }

            $all = array_merge($all, $children);
            $current = $children;
        }

        return $all;
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
    }

}

?>
