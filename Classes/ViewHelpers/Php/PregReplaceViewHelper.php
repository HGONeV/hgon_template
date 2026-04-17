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
 * PregReplaceViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class PregReplaceViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument(
            'pattern',
            'string',
            'PCRE pattern without delimiters',
            true
        );

        $this->registerArgument(
            'replacement',
            'string',
            'Replacement string',
            true
        );

        $this->registerArgument(
            'subject',
            'string',
            'Input string',
            true
        );
    }

    public function render(): string
    {
        $pattern = '/' . $this->arguments['pattern'] . '/';

        return (string)preg_replace(
            $pattern,
            $this->arguments['replacement'],
            strip_tags($this->arguments['subject'])
        );
    }
}
