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
 * Class DidYouKnowRepository
 *
 * @author Maximilian Fäßler <faesslerweb@web.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DidYouKnowRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * Find by sysCategory
     *
     * @param \HGON\HgonTemplate\Domain\Model\SysCategory $sysCategory
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findBySysCategory($sysCategory)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->contains('sysCategory', [$sysCategory])
        );

        return $query->execute();
        //===
    }
}