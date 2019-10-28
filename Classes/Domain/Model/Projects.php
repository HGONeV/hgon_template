<?php

namespace HGON\HgonTemplate\Domain\Model;

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
 * Class Projects
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Projects extends \RKW\RkwProjects\Domain\Model\Projects
{
    /**
     * projectPid
     *
     * Overwrite:   One the one hand, the original ObjectStorage doesn't works
     *              On the other hand it even doesn't makes sense
     *              And more: We can't work with Objects, because its typolink
     *
     * @var string
     */
    protected $projectPid = null;

    /**
     * pages
     *
     * @var \HGON\HgonTemplate\Domain\Model\Pages
     */
    protected $pages = null;

    /**
     * projectManager
     *
     * Overwrite: The ObjectStorage doesn't makes sense.
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HGON\HgonTemplate\Domain\Model\Authors>
     */
    protected $projectManager = null;

    /**
     * __construct
     */
    public function __construct()
    {
        Parent::__construct();
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->projectManager = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }


    /**
     * Returns the projectPid
     *
     * @return string $projectPid
     */
    public function getProjectPid()
    {
        return $this->projectPid;
    }

    /**
     * Sets the projectPid
     *
     * @param string $projectPid
     * @return void
     */
    public function setProjectPid($projectPid)
    {
        $this->projectPid = $projectPid;
    }

    /**
     * Returns the pages
     *
     * @return \HGON\HgonTemplate\Domain\Model\Pages $pages
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Sets the pages
     *
     * @param \HGON\HgonTemplate\Domain\Model\Pages $pages
     * @return void
     */
    public function setPages(\HGON\HgonTemplate\Domain\Model\Pages $pages)
    {
        $this->pages = $pages;
    }

    /**
     * Returns the projectManager
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HGON\HgonTemplate\Domain\Model\Authors> $projectManager
     */
    public function getProjectManager()
    {
        return $this->projectManager;
    }

    /**
     * Sets the projectManager
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HGON\HgonTemplate\Domain\Model\Authors> $projectManager
     * @return void
     */
    public function setProjectManager(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $projectManager)
    {
        $this->projectManager = $projectManager;
    }

}