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
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * ObjectTypeViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ObjectTypeViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{

    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument(
            'object',
            'mixed',
            'The object whose type should be returned',
            true
        );
    }

    /**
     * returns the last element of the namespace (e.g. "Pages", or "SysCategory")
     *
     * @param mixed object
     *
     * @return string
     */
    public function render(): string
    {
        $object = $this->arguments['object'];

        // Array?
        if (is_array($object)) {
            return 'array';
        }

        // Kein Objekt → "unknown"
        if (!is_object($object)) {
            return 'unknown';
        }

        // Klassennamen in Teile splitten
        $parts = GeneralUtility::trimExplode('\\', get_class($object), true);

        return end($parts) ?: 'unknown';
    }


}
