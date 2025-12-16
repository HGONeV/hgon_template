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
use TYPO3\CMS\Core\Session\SessionManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * IterateKeyWithSessionViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IterateKeyWithSessionViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Dieser ViewHelper gibt nur einen Integer zurück – kein Child-Content.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument(
            'total',
            'int',
            'Total number of items to iterate over',
            true
        );

        $this->registerArgument(
            'uniqueName',
            'string',
            'Unique session key name',
            true
        );
    }


    /**
     * @return int
     */
    public function render(): int
    {
        $total = (int)($this->arguments['total'] ?? 0);
        $uniqueName = (string)($this->arguments['uniqueName'] ?? '');

        if ($total <= 0 || $uniqueName === '') {
            return 0;
        }

        // TYPO3 10.4: TSFE ist noch verfügbar (wenn auch "legacy"), aber storeSessionData() ist weg.
        /** @var FrontendUserAuthentication|null $feUser */
        $feUser = $GLOBALS['TSFE']->fe_user ?? null;

        if (!$feUser instanceof FrontendUserAuthentication) {
            // Kein FE-Context / kein FE-User-Objekt -> sauberer Fallback
            return 0;
        }

        $current = (int)($feUser->getKey('ses', $uniqueName) ?: 0);

        if ($current < 1) {
            $current = 1;
        } elseif ($current < $total) {
            $current++;
        } else {
            $current = 1;
        }

        $feUser->setKey('ses', $uniqueName, $current);

        // In TYPO3 10 wird die FE-Session normalerweise am Ende des Requests persistiert.
        // Wenn du in deiner konkreten Nutzung "sofort" persistieren musst (z.B. Redirect),
        // kannst du das explizit über den SessionManager tun:


        return $current;
    }

}
