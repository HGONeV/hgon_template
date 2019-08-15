<?php
namespace HGON\HgonTemplate\ViewHelpers\Php;
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
 * PregReplaceViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PregReplaceViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Imports php function preg_match
     *
     * @param mixed $pattern
     * @param mixed $replacement
     * @param mixed $subject
     *
     * @return mixed
     */
    public function render($pattern, $replacement, $subject)
    {
        return preg_replace("/$pattern/", $replacement, strip_tags($subject));
        //===
    }


}