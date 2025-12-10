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
        $total = (int)$this->arguments['total'];
        $uniqueName = (string)$this->arguments['uniqueName'];

        /** @var FrontendUserAuthentication|null $feUser */
        $feUser = $GLOBALS['TSFE']->fe_user ?? null;

        if (!$feUser instanceof FrontendUserAuthentication || $total <= 0) {
            // Fallback – im Zweifel lieber 0 zurückgeben
            return 0;
        }

        // aktuellen Wert aus der Session holen
        $current = (int)($feUser->getKey('ses', $uniqueName) ?: 0);

        if ($current < 1) {
            // Startwert
            $current = 1;
        } elseif ($current < $total) {
            // hochzählen
            $current++;
        } else {
            // zurück auf Anfang
            $current = 1;
        }

        $feUser->setKey('ses', $uniqueName, $current);
        // optional: Session speichern, falls du auf Nummer sicher gehen willst
        $feUser->storeSessionData();

        return $current;
    }


}
