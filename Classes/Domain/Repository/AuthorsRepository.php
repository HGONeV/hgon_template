<?php

namespace HGON\HgonTemplate\Domain\Repository;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
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
 * Class AuthorsRepository
 *
 * @author Maximilian Fäßler <faesslerweb@web.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AuthorsRepository extends \RKW\RkwAuthors\Domain\Repository\AuthorsRepository
{
    /**
     * Find by multiple uids
     *
     * @param string $uidList
     * @return array
     */
    public function findByUidList($uidList)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->in('uid', GeneralUtility::trimExplode(',', $uidList))
        );

        return $query->execute()->toArray();
        //===
    }

}