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

/**
 * TtContent
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package Hgon_Template
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TtContent extends \RKW\RkwNewsletter\Domain\Model\TtContent
{
    /**
     * assets (new "Image" fields of textMedia)
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $assets;


    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct();
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
        $this->assets = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }


    /**
     * Adds a backend user to the newsletter
     *
     * @param \RKW\RkwBasics\Domain\Model\FileReference $image
     * @return void
     * @api
     */
    public function addAssets(\RKW\RkwBasics\Domain\Model\FileReference $image)
    {
        $this->assets->attach($image);
    }

    /**
     * Removes a backend user from the newsletter
     *
     * @param \RKW\RkwBasics\Domain\Model\FileReference $image
     * @return void
     * @api
     */
    public function removeAssets(\RKW\RkwBasics\Domain\Model\FileReference $image)
    {
        $this->assets->detach($image);
    }

    /**
     * Returns the backend user.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage An object storage containing the backend user
     * @api
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * Sets the backend user.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $image
     * @return void
     * @api
     */
    public function setAssets(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $image)
    {
        $this->assets = $image;
    }
}