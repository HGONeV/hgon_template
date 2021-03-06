<?php

namespace HGON\HgonTemplate\Domain\Repository;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
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
 * Class ProjectsRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ProjectsRepository extends \RKW\RkwProjects\Domain\Repository\ProjectsRepository
{
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



    /**
     * Find projects for donation proposals
     *
     * @param integer $pageNumber
     * @param integer $limit
     * @return array
     */
    public function findByFilter($pageNumber = 1, $limit = 5)
    {
        $query = $this->createQuery();

        // Offset
        $offset = ((intval($pageNumber) - 1) * $limit);
        if ($pageNumber <= 1) {
            $offset = 0;
        }
        // For offset issue on limit 1
        if ($pageNumber > 1 && $limit == 1) {
            $offset -= 1;
        }

        $query->setLimit($limit);
        $query->setOffset($offset);

        return $query->execute();
        //===
    }

}