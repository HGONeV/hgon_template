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
 * Class EventReservation
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EventReservation extends \RKW\RkwEvents\Domain\Model\EventReservation
{
    /**
     * txHgontemplateEventcosts
     *
     * @var string
     */
    protected $txHgontemplateEventcosts = null;

    /**
     * txHgontemplateEventculinary
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HGON\HgonTemplate\Domain\Model\EventCulinary>
     */
    protected $txHgontemplateEventculinary = null;

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
        parent::initStorageObjects();
        $this->addPerson = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->txHgontemplateEventculinary = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * @return string
     */
    public function getTxHgontemplateEventcosts()
    {
        return $this->txHgontemplateEventcosts;
    }

    /**
     * @param string $txHgontemplateEventcosts
     */
    public function setTxHgontemplateEventcosts($txHgontemplateEventcosts)
    {
        $this->txHgontemplateEventcosts = $txHgontemplateEventcosts;
    }

    /**
     * Adds a EventCulinary
     *
     * @param \HGON\HgonTemplate\Domain\Model\EventCulinary $txHgontemplateEventculinary
     * @return void
     */
    public function addtxHgontemplateEventculinary(\HGON\HgonTemplate\Domain\Model\EventCulinary $txHgontemplateEventculinary)
    {
        $this->txHgontemplateEventculinary->attach($txHgontemplateEventculinary);
    }

    /**
     * Removes a EventCulinary
     *
     * @param \HGON\HgonTemplate\Domain\Model\EventCulinary $txHgontemplateEventculinary
     * @return void
     */
    public function removetxHgontemplateEventculinary(\HGON\HgonTemplate\Domain\Model\EventCulinary $txHgontemplateEventculinary)
    {
        $this->txHgontemplateEventculinary->detach($txHgontemplateEventculinary);
    }

    /**
     * Returns the txHgontemplateEventculinary
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HGON\HgonTemplate\Domain\Model\EventCulinary> txHgontemplateEventculinary
     */
    public function gettxHgontemplateEventculinary()
    {
        return $this->txHgontemplateEventculinary;
    }

    /**
     * Sets the txHgontemplateEventculinary
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HGON\HgonTemplate\Domain\Model\EventCulinary> $txHgontemplateEventculinary
     */
    public function settxHgontemplateEventculinary(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $txHgontemplateEventculinary)
    {
        $this->txHgontemplateEventculinary = $txHgontemplateEventculinary;
    }

}