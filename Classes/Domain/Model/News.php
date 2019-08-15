<?php

namespace HGON\HgonTemplate\Domain\Model;
    /*
     * This file is part of the TYPO3 CMS project.
     *
     * It is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License, either version 3
     * of the License, or any later version.
     *
     * For the full copyright and license information, please read the
     * LICENSE.txt file that was distributed with this source code.
     *
     * The TYPO3 project - inspiring people to share!
     */

/**
 * Class News
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class News extends \GeorgRinger\News\Domain\Model\News
{
    /**
     * txRkwprojectProject
     *
     * @var \HGON\HgonTemplate\Domain\Model\Projects
     */
    protected $txRkwprojectProject = null;

    /**
     * Returns the txRkwprojectProject
     *
     * @return \HGON\HgonTemplate\Domain\Model\Projects
     */
    public function getTxRkwprojectProject()
    {
        return $this->txRkwprojectProject;
    }

    /**
     * Sets the txRkwprojectProject
     *
     * @param \HGON\HgonTemplate\Domain\Model\Projects $txRkwprojectProject
     * @return void
     */
    public function setTxRkwprojectProject(\HGON\HgonTemplate\Domain\Model\Projects $txRkwprojectProject)
    {
        $this->txRkwprojectProject = $txRkwprojectProject;
    }
}