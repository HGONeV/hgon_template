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

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * RandViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class RandViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument(
            'min',
            'int',
            'Minimum value',
            false,
            null
        );

        $this->registerArgument(
            'max',
            'int',
            'Maximum value',
            false,
            null
        );
    }

    public function render(): int
    {
        $min = $this->arguments['min'];
        $max = $this->arguments['max'];

        if ($min !== null && $max !== null) {
            return random_int($min, $max);
        }

        return random_int(PHP_INT_MIN, PHP_INT_MAX);
    }
}
