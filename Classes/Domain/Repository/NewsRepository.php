<?php

namespace HGON\HgonTemplate\Domain\Repository;

/**
 * This file is part of the "news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * News repository with all the callable functionality
 */
class NewsRepository extends \GeorgRinger\News\Domain\Repository\NewsRepository
{
    protected $defaultOrderings = array(
        'datetime' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
    );

    public function initializeObject()
    {
        /** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
        $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
        // go for $defaultQuerySettings = $this->createQuery()->getQuerySettings(); if you want to make use of the TS persistence.storagePid with defaultQuerySettings(), see #51529 for details

        // don't add the pid constraint
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * rewrite findAll (type independent)
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     */
    public function findAll()
    {
        $query = $this->createQuery();

        return $query->execute();
        //===
    }



    /**
     * Get news with certain options
     * -> If no parameter are given, we just have a findAll with offset and limit
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage|array $sysCategoryList
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage|array $workgroupList
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage|array $excludedNews
     * @param integer $pageNumber
     * @param integer $limit
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByFilter($sysCategoryList = [], $workgroupList = [], $excludedNews = [], $pageNumber = 1, $limit = 5)
    {
        // if single item is given, prepare for "in"-Query
        if ($sysCategoryList instanceof \HGON\HgonTemplate\Domain\Model\SysCategory) {
            $sysCategoryList = [$sysCategoryList];
        }

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        // Offset
        $offset = ((intval($pageNumber) - 1) * $limit);
        if ($pageNumber <= 1) {
            $offset = 0;
        }
        // For offset issue on limit 1
        if ($pageNumber > 1 && $limit == 1) {
            $offset -= 1;
        }

        $constraints = [];
        if ($sysCategoryList) {
            $constraints[] = $query->in('categories.uid', $sysCategoryList);
        }

        if ($workgroupList) {
            $constraints[] = $query->in('tx_hgon_workgroup', $workgroupList);
        }

        if ($excludedNews) {
            $constraints[] = $query->logicalNot($query->in('uid', $excludedNews));
        }

        // build query
        if ($sysCategoryList || $workgroupList || $excludedNews) {
            $query->matching($query->logicalAnd($constraints));
        }

        $query->setLimit($limit);
        $query->setOffset($offset);

        return $query->execute();
        //===

    }



    /**
     * Get news with searched categories
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage|array $sysCategoryList
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage|array $excludeNewsList
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByCategories($sysCategoryList = [], $excludeNewsList = [])
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $constraints = [];

        if ($sysCategoryList) {
            $constraints[] = $query->in('categories.uid', $sysCategoryList);
        }

        if ($excludeNewsList) {
            $constraints[] = $query->logicalNot($query->in('uid', $excludeNewsList));
        }

        if ($sysCategoryList || $excludeNewsList) {
            $query->matching($query->logicalAnd($constraints));
        }

        return $query->execute();
        //===
    }



    /**
     * Find news optional except a special one
     * (is a ordinary findAll, if no news is set)
     *
     * @param \HGON\HgonTemplate\Domain\Model\News $news
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     */
    public function findAllExceptCurrent(\HGON\HgonTemplate\Domain\Model\News $news = null)
    {
        $query = $this->createQuery();

        if ($news instanceof \HGON\HgonTemplate\Domain\Model\News) {
            $query->matching(
                $query->logicalNot(
                    $query->equals('uid', $news->getUid())
                )
            );
        }

        return $query->execute();
        //===
    }

}
