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
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * IterateKeyWithSessionViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IterateKeyWithSessionViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Imports php function rand
     *
     * @param integer $total
     * @param string $uniqueName
     *
     * @return integer
     */
    public function render($total, $uniqueName)
    {
        // Initial: Set start value
        if (!$GLOBALS['TSFE']->fe_user->getKey('ses', $uniqueName)) {
            // image array starts with 1
            $GLOBALS['TSFE']->fe_user->setKey('ses', $uniqueName, 1);

            // Else: Increase (and maybe reset) value and return
        } else {
            if ($GLOBALS['TSFE']->fe_user->getKey('ses', $uniqueName) < $total) {
                // increase
                $GLOBALS['TSFE']->fe_user->setKey('ses', $uniqueName, $GLOBALS['TSFE']->fe_user->getKey('ses', $uniqueName) + 1);
            } else {
                // restart from beginning
                // image array starts with 1
                $GLOBALS['TSFE']->fe_user->setKey('ses', $uniqueName, 1);
            }
        }
        return $GLOBALS['TSFE']->fe_user->getKey('ses', $uniqueName);
        //===
    }


}