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
class CreateRowsViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{

    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument(
            'list',
            'mixed',
            'List of items or list of lists (arrays/QueryResult/ObjectStorage)',
            true
        );

        $this->registerArgument(
            'itemsPerRow',
            'int',
            'Number of items per row',
            false,
            3
        );

        $this->registerArgument(
            'shuffle',
            'bool',
            'Whether to shuffle the merged list before creating rows',
            false,
            false
        );
    }

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
    public function render(): array
    {
        $list        = $this->arguments['list'];
        $itemsPerRow = (int)$this->arguments['itemsPerRow'];
        $shuffle     = (bool)$this->arguments['shuffle'];

        // falls jemand nur eine einzelne Liste übergibt, in ein Array packen
        if (!is_array($list)) {
            $list = [$list];
        }

        // merge possible list-arrays
        $mergedList = [];
        foreach (array_filter($list) as $singleList) {
            if ($singleList instanceof QueryResult || $singleList instanceof ObjectStorage) {
                $singleList = $singleList->toArray();
            }

            if (!is_array($singleList)) {
                $singleList = [$singleList];
            }

            $mergedList = array_merge($mergedList, $singleList);

            // shuffle results if wanted (Originalverhalten beibehalten)
            if ($shuffle) {
                shuffle($mergedList);
            }
        }

        if ($itemsPerRow <= 0) {
            // Schutz vor Division durch 0 / Endlosschleifen
            return [$mergedList];
        }

        // create new list with rows in wished length (default: 3 items per row)
        $newList = [];
        $i = 0;

        do {
            $remaining = count($mergedList);
            if ($remaining > $itemsPerRow) {
                $newList[$i] = array_splice($mergedList, 0, $itemsPerRow);
            } else {
                // put last elements into array
                $newList[$i] = array_splice($mergedList, 0, $remaining);
            }
            $i++;
        } while (count($mergedList) > 0);

        return $newList;
    }


}
