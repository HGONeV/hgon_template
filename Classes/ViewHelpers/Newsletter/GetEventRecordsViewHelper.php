<?php
namespace HGON\HgonTemplate\ViewHelpers\Newsletter;
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
use \TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;
use \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * GetEventRecordsViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetEventRecordsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Gets event records of the newsletter
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function render(\RKW\RkwNewsletter\Domain\Model\Issue $issue)
    {
        return static::renderStatic(
            array(
                'issue' => $issue,
            ),
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
        //===
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface|\TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
        $issue = $arguments['issue'];
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

        /** @var \HGON\HgonTemplate\Domain\Repository\NewsletterRepository $newsletterRepository */
        $newsletterRepository = $objectManager->get('HGON\\HgonTemplate\\Domain\\Repository\\NewsletterRepository');
        /** @var \HGON\HgonTemplate\Domain\Repository\EventRepository $eventRepository */
        $eventRepository = $objectManager->get('HGON\\HgonTemplate\\Domain\\Repository\\EventRepository');

        // get HGON newsletter
        /** @var \HGON\HgonTemplate\Domain\Model\Newsletter $hgonNewsletter */
        $hgonNewsletter = $newsletterRepository->findByUid($issue->getNewsletter()->getUid());

        // 1 = show automatically selected items by given count
        if ($hgonNewsletter->getTxHgontemplateEventSelect() == 1) {
            return $eventRepository->findUpcoming($hgonNewsletter->getTxHgontemplateEventCount());
            //===
        }

        // 2 = show manual selected items
        if ($hgonNewsletter->getTxHgontemplateEventSelect() == 2) {
            return $hgonNewsletter->getTxHgontemplateEventList();
            //===
        }

        // 0 = show nothing
        /* -> logically not necessary
        if (!$hgonNewsletter->getTxHgontemplateNewsSelect()) {
            return false;
            //===
        }*/

        // return countable
        return [];
        //===
    }

}