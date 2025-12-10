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
 * IsValidEmailAddressViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IsValidEmailAddressViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{

    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument(
            'emailAddress',
            'string',
            'Email address to validate',
            true
        );
    }

    /**
     * @param string $emailAddress
     *
     * @return integer
     */
    public function render(): bool
    {
        $email = trim((string)$this->arguments['emailAddress']);

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }


}
