<?php

namespace HGON\HgonTemplate\Domain\Repository;

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
 * Class ArticleRepository
 *
 * @author Maximilian Fäßler <faesslerweb@web.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ArticleRepository extends \HGON\HgonPayment\Domain\Repository\ArticleRepository
{
    protected $defaultOrderings = array(
        'crdate' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
    );

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
}