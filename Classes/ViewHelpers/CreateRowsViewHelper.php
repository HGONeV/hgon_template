<?php
namespace HGON\HgonTemplate\ViewHelpers;
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
use Doctrine\Common\Util\Debug;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * CreateRowsViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CreateRowsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * create rows (for automatical handling if we need c4, c6, c8 or c12 boxes for the last items)
     * -> we can calculate the html item-type, if we know, how many items we have in a row
     *
     * @param mixed $list
     * @param int $itemsPerRow
     * @param bool $shuffle
     *
     * @return array
     */
    public function render($list, $itemsPerRow = 3, $shuffle = false)
    {
        // merge possible list-arrays
        $mergedList = [];
        foreach (array_filter($list) as $singleList) {
            if (
                $singleList instanceof \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult
                || $singleList instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage
            ) {
                $singleList = $singleList->toArray();
            }
            if (!is_array($singleList)) {
                $singleList = [$singleList];
            }
            $mergedList = array_merge($mergedList, $singleList);

            // shuffle results if wanted
            if ($shuffle) {
                shuffle($mergedList);
            }
        }

        // create new list with rows in wished length (default: 3 items per row)
        $newList = [];
        $i = 0;
        do {
            if (count($mergedList) > $itemsPerRow) {
                $newList[$i] = array_splice($mergedList, 0, $itemsPerRow);
            } else {
                // put last elements into array
                $newList[$i] = array_splice($mergedList, 0, count($mergedList));
            }
            $i++;
        } while (count($mergedList));

        return $newList;
        //===
    }


}