<?php

namespace HGON\HgonTemplate\Domain\Repository;

/**
 * This file is part of the "news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use HGON\HgonTemplate\Utility\Common;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
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
        $querySettings = $this->createQuery()->getQuerySettings();

        if ($querySettings instanceof Typo3QuerySettings) {
            $querySettings->setRespectStoragePage(false);
            $this->setDefaultQuerySettings($querySettings);
        }
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
    public function findByFilter(
        $sysCategoryList = [],
        array $workgroupList = [],
        array $excludedNews = [],
        int $pageNumber = 1,
        int $limit = 5
    ): QueryResultInterface {
        // Single SysCategory -> array
        if ($sysCategoryList instanceof \HGON\HgonTemplate\Domain\Model\SysCategory) {
            $sysCategoryList = [$sysCategoryList];
        }

        // Normalize categories: allow objects OR ints
        if (is_array($sysCategoryList) && $sysCategoryList !== []) {
            $sysCategoryList = array_values(array_filter(array_map(
                static function ($item) {
                    if ($item instanceof \HGON\HgonTemplate\Domain\Model\SysCategory) {
                        return (int)$item->getUid();
                    }
                    if (is_numeric($item)) {
                        return (int)$item;
                    }
                    return null;
                },
                $sysCategoryList
            ), static fn($v) => $v !== null && $v > 0));
        } else {
            $sysCategoryList = [];
        }

        // Normalize excludedNews to ints
        if ($excludedNews !== []) {
            $excludedNews = array_values(array_filter(array_map(
                static fn($v) => is_numeric($v) ? (int)$v : null,
                $excludedNews
            ), static fn($v) => $v !== null && $v > 0));
        }

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        // Offset
        $offset = max(0, ($pageNumber - 1) * $limit);
        if ($pageNumber > 1 && $limit === 1) {
            $offset = max(0, $offset - 1);
        }

        $constraints = [];

        if ($sysCategoryList !== []) {
            $constraints[] = $query->in('categories.uid', $sysCategoryList);
        }

        if ($workgroupList !== []) {
            $workgroupConstraints = [];
            foreach ($workgroupList as $wg) {
                $workgroupConstraints[] = $query->contains('txHgonWorkgroup', $wg);
            }
            if ($workgroupConstraints !== []) {
                $constraints[] = $query->logicalOr(...$workgroupConstraints);
            }
        }

        if ($excludedNews !== []) {
            $constraints[] = $query->logicalNot(
                $query->in('uid', $excludedNews)
            );
        }

        if ($constraints !== []) {
            $query->matching(
                count($constraints) === 1 ? $constraints[0] : $query->logicalAnd(...$constraints)
            );
        }

        $query->setLimit($limit);
        $query->setOffset($offset);

        return $query->execute();
    }


    /**
     * Get news with searched categories
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage|array $sysCategoryList
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage|array $excludeNewsList
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByCategories(
        iterable $sysCategoryList = [],
        iterable $excludeNewsList = []
    ) {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $constraints = [];

        $categoryUids = Common::normalizeToUidArray($sysCategoryList);
        if ($categoryUids !== []) {
            $constraints[] = $query->in('categories.uid', $categoryUids);
        }

        $excludeUids = Common::normalizeToUidArray($excludeNewsList);
        if ($excludeUids !== []) {
            $constraints[] = $query->logicalNot(
                $query->in('uid', $excludeUids)
            );
        }

        if ($constraints !== []) {
            $query->matching(
                count($constraints) === 1
                    ? $constraints[0]
                    : $query->logicalAnd(...$constraints)
            );
        }

        $query->setLimit(10);

        return $query->execute();
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



    /**
     * Find a count of news until a set max date
     *
     * @param integer
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     */
    public function findByMaxDate($maxTimestamp, $count = 3)
    {
        $query = $this->createQuery();

        $query->matching(
            $query->lessThanOrEqual('datetime', $maxTimestamp)
        );

        $query->setLimit($count);

        return $query->execute();
        //===
    }

}
