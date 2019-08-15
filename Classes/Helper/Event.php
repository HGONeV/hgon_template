<?php
namespace HGON\HgonTemplate\Helper;

/***
 *
 * This file is part of the "HGON Template" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2018 Maximilian Fäßler <maximilian@faesslerweb.de>, Fäßler Web UG
 *
 ***/
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use HGON\HgonTemplate\Domain\Model\SysCategory;

/**
 * Journal
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Event implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * createMonthListArray
     * function which returns the current and upcoming months for filter sorting
     * returning timestamps (for january the function will return 01.01.; for february 01.02. etc... as timestamp)
     *
     * @param integer $monthsToShow how many upcoming months include current
     * @return array
     */
    public static function createMonthListArray($monthsToShow = 6)
    {
        $resultArray = [];

        for ($i = 0; $i <= $monthsToShow; $i++) {
            $month = strtotime(date('Y-m-01', mktime(0, 0, 0, date('m') + $i, 1, date('Y'))));
            setlocale(LC_TIME, "de_DE.UTF8");
            $resultArray[$month] = strftime('%B %Y', $month);
        }

        return $resultArray;
        //===
    }
}