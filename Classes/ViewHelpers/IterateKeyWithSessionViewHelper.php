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

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Session\SessionManager;
use TYPO3\CMS\Core\Session\UserSession;
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

        /** @var ServerRequestInterface|null $request */
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return 0;
        }

        $feUser = $request->getAttribute('frontend.user');
        if (!$feUser instanceof FrontendUserAuthentication) {
            return 0;
        }

        /** @var UserSession|null $session */
        $session = $request->getAttribute('frontend.user.session');
        // Fallback, falls das Attribut in deinem Kontext nicht gesetzt ist:
        if (!$session instanceof UserSession) {
            $current = (int)($feUser->getKey('ses', $uniqueName) ?: 0);
        } else {
            $current = (int)($session->get($uniqueName) ?? 0);
        }

        if ($current < 1) {
            $current = 1;
        } elseif ($current < $total) {
            $current++;
        } else {
            $current = 1;
        }

        if ($session instanceof UserSession) {
            $session->set($uniqueName, $current);
        } else {
            $feUser->setKey('ses', $uniqueName, $current);
        }

        return $current;
    }

}
