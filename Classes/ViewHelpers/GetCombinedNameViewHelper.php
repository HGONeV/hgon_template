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

use \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use \TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;
use \TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class GetCombinedNameViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package Hgon_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetCombinedNameViewHelper extends AbstractViewHelper implements CompilableInterface
{

    /**
     * Build the full name
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Authors $author
     * @param boolean $addTitleBefore
     * @param boolean $addTitleAfter
     * @return string
     */
    public function render($author, $addTitleBefore = true, $addTitleAfter = false)
    {

        return static::renderStatic(
            array(
                'author'         => $author,
                'addTitleBefore' => $addTitleBefore,
                'addTitleAfter'  => $addTitleAfter,
            ),
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
        //===
    }


    /**
     * Static rendering
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $author = $arguments['author'];
        $addTitleBefore = $arguments['addTitleBefore'];
        $addTitleAfter = $arguments['addTitleAfter '];

        $name = '';
        if (
            ($author instanceof \RKW\RkwAuthors\Domain\Model\Authors)
            || ($author instanceof \RKW\RkwSearch\OrientDb\Domain\Model\DocumentAuthors)
        ) {
            $name = $author->getLastName() ? $author->getLastName() : $author->getLastname();

            if ($author->getMiddleName() || $author->getMiddleName()) {
                $name = ($author->getMiddleName() ? $author->getMiddleName() : $author->getMiddlename()) . ' ' . $name;
            }

            if ($author->getFirstName() || $author->getFirstname()) {
                $name = ($author->getFirstName() ? $author->getFirstName() : $author->getFirstname()) . ' ' . $name;
            }

            if ($addTitleBefore) {
                if ($author->getTitleBefore()) {
                    $name = $author->getTitleBefore() . ' ' . $name;
                }
            }

            if ($addTitleAfter) {
                if ($author->getTitleAfter()) {
                    $name .= ', ' . $author->getTitleAfter();
                }
            }
        }

        return $name;
        //===
    }

}