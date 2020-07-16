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
 * Newsletter
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Newsletter extends \RKW\RkwNewsletter\Domain\Model\Newsletter
{
    /**
     * txHgontemplateNewsSelect
     *
     * @var integer
     */
    protected $txHgontemplateNewsSelect = 0;

    /**
     * txHgontemplateNewsCount
     *
     * @var integer
     */
    protected $txHgontemplateNewsCount = 0;

    /**
     * txHgontemplateArticleSelect
     *
     * @var integer
     */
    protected $txHgontemplateArticleSelect = 0;

    /**
     * txHgontemplateArticleCount
     *
     * @var integer
     */
    protected $txHgontemplateArticleCount = 0;

    /**
     * txHgontemplateEventSelect
     *
     * @var integer
     */
    protected $txHgontemplateEventSelect = 0;

    /**
     * txHgontemplateEventCount
     *
     * @var integer
     */
    protected $txHgontemplateEventCount = 0;

    /**
     * txHgontemplateNewsList
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HGON\HgonTemplate\Domain\Model\News>
     */
    protected $txHgontemplateNewsList;

    /**
     * txHgontemplateArticleList
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HGON\HgonTemplate\Domain\Model\Article>
     */
    protected $txHgontemplateArticleList;

    /**
     * txHgontemplateEventList
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HGON\HgonTemplate\Domain\Model\Event>
     */
    protected $txHgontemplateEventList;

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
        $this->txHgontemplateNewsList = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->txHgontemplateArticleList = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->txHgontemplateEventList = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

    }

    /**
     * Returns the txHgontemplateNewsSelect
     *
     * @return int $txHgontemplateNewsSelect
     */
    public function getTxHgontemplateNewsSelect()
    {
        return $this->txHgontemplateNewsSelect;
    }

    /**
     * Sets the txHgontemplateNewsSelect
     *
     * @param int $txHgontemplateNewsSelect
     * @return void
     */
    public function setTxHgontemplateNewsSelect($txHgontemplateNewsSelect)
    {
        $this->txHgontemplateNewsSelect = $txHgontemplateNewsSelect;
    }

    /**
     * Returns the txHgontemplateNewsCount
     *
     * @return int $txHgontemplateNewsCount
     */
    public function getTxHgontemplateNewsCount()
    {
        return $this->txHgontemplateNewsCount;
    }

    /**
     * Sets the txHgontemplateNewsCount
     *
     * @param int $txHgontemplateNewsCount
     * @return void
     */
    public function setTxHgontemplateNewsCount($txHgontemplateNewsCount)
    {
        $this->txHgontemplateNewsCount = $txHgontemplateNewsCount;
    }

    /**
     * Returns the txHgontemplateArticleSelect
     *
     * @return int $txHgontemplateArticleSelect
     */
    public function getTxHgontemplateArticleSelect()
    {
        return $this->txHgontemplateArticleSelect;
    }

    /**
     * Sets the txHgontemplateArticleSelect
     *
     * @param int $txHgontemplateArticleSelect
     * @return void
     */
    public function setTxHgontemplateArticleSelect($txHgontemplateArticleSelect)
    {
        $this->txHgontemplateArticleSelect = $txHgontemplateArticleSelect;
    }

    /**
     * Returns the txHgontemplateArticleCount
     *
     * @return int $txHgontemplateArticleCount
     */
    public function getTxHgontemplateArticleCount()
    {
        return $this->txHgontemplateArticleCount;
    }

    /**
     * Sets the txHgontemplateArticleCount
     *
     * @param int $txHgontemplateArticleCount
     * @return void
     */
    public function setTxHgontemplateArticleCount($txHgontemplateArticleCount)
    {
        $this->txHgontemplateArticleCount = $txHgontemplateArticleCount;
    }

    /**
     * Returns the txHgontemplateEventSelect
     *
     * @return int $txHgontemplateEventSelect
     */
    public function getTxHgontemplateEventSelect()
    {
        return $this->txHgontemplateEventSelect;
    }

    /**
     * Sets the txHgontemplateEventSelect
     *
     * @param int $txHgontemplateEventSelect
     * @return void
     */
    public function setTxHgontemplateEventSelect($txHgontemplateEventSelect)
    {
        $this->txHgontemplateEventSelect = $txHgontemplateEventSelect;
    }

    /**
     * Returns the txHgontemplateEventCount
     *
     * @return int $txHgontemplateEventCount
     */
    public function getTxHgontemplateEventCount()
    {
        return $this->txHgontemplateEventCount;
    }

    /**
     * Sets the txHgontemplateEventCount
     *
     * @param int $txHgontemplateEventCount
     * @return void
     */
    public function setTxHgontemplateEventCount($txHgontemplateEventCount)
    {
        $this->txHgontemplateEventCount = $txHgontemplateEventCount;
    }

    /**
     * Adds a txHgontemplateNewsList to the newsletter
     *
     * @param \HGON\HgonTemplate\Domain\Model\News $txHgontemplateNewsList
     * @return void
     * @api
     */
    public function addTxHgontemplateNewsList(\HGON\HgonTemplate\Domain\Model\News $txHgontemplateNewsList)
    {
        $this->txHgontemplateNewsList->attach($txHgontemplateNewsList);
    }

    /**
     * Removes a txHgontemplateNewsList from the newsletter
     *
     * @param \HGON\HgonTemplate\Domain\Model\News $txHgontemplateNewsList
     * @return void
     * @api
     */
    public function removeTxHgontemplateNewsList(\HGON\HgonTemplate\Domain\Model\News $txHgontemplateNewsList)
    {
        $this->topic->detach($txHgontemplateNewsList);
    }

    /**
     * Returns the txHgontemplateNewsList. Keep in mind that the property is called "txHgontemplateNewsList"
     * although it can hold several txHgontemplateNewsList.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage An object storage containing the txHgontemplateNewsList
     * @api
     */
    public function getTxHgontemplateNewsList()
    {
        return $this->txHgontemplateNewsList;
    }

    /**
     * Sets the txHgontemplateNewsList. Keep in mind that the property is called "txHgontemplateNewsList"
     * although it can hold several txHgontemplateNewsList.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $txHgontemplateNewsList
     * @return void
     * @api
     */
    public function setTxHgontemplateNewsList(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $txHgontemplateNewsList)
    {
        $this->txHgontemplateNewsList = $txHgontemplateNewsList;
    }

    /**
     * Adds a txHgontemplateArticleList to the newsletter
     *
     * @param \HGON\HgonTemplate\Domain\Model\Article $txHgontemplateArticleList
     * @return void
     * @api
     */
    public function addTxHgontemplateArticleList(\HGON\HgonTemplate\Domain\Model\Article $txHgontemplateArticleList)
    {
        $this->txHgontemplateArticleList->attach($txHgontemplateArticleList);
    }

    /**
     * Removes a txHgontemplateArticleList from the newsletter
     *
     * @param \HGON\HgonTemplate\Domain\Model\Article $txHgontemplateArticleList
     * @return void
     * @api
     */
    public function removeTxHgontemplateArticleList(\HGON\HgonTemplate\Domain\Model\Article $txHgontemplateArticleList)
    {
        $this->topic->detach($txHgontemplateArticleList);
    }

    /**
     * Returns the txHgontemplateArticleList. Keep in mind that the property is called "txHgontemplateArticleList"
     * although it can hold several txHgontemplateArticleList.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage An object storage containing the txHgontemplateArticleList
     * @api
     */
    public function getTxHgontemplateArticleList()
    {
        return $this->txHgontemplateArticleList;
    }

    /**
     * Sets the txHgontemplateNewsList. Keep in mind that the property is called "txHgontemplateArticleList"
     * although it can hold several txHgontemplateArticleList.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $txHgontemplateArticleList
     * @return void
     * @api
     */
    public function setTxHgontemplateArticleList(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $txHgontemplateArticleList)
    {
        $this->txHgontemplateArticleList = $txHgontemplateArticleList;
    }

    /**
     * Adds a txHgontemplateEventList to the newsletter
     *
     * @param \HGON\HgonTemplate\Domain\Model\Event $txHgontemplateEventList
     * @return void
     * @api
     */
    public function addTxHgontemplateEventList(\HGON\HgonTemplate\Domain\Model\Event $txHgontemplateEventList)
    {
        $this->txHgontemplateEventList->attach($txHgontemplateEventList);
    }

    /**
     * Removes a txHgontemplateEventList from the newsletter
     *
     * @param \HGON\HgonTemplate\Domain\Model\Event $txHgontemplateEventList
     * @return void
     * @api
     */
    public function removeTxHgontemplateEventList(\HGON\HgonTemplate\Domain\Model\Event $txHgontemplateEventList)
    {
        $this->topic->detach($txHgontemplateEventList);
    }

    /**
     * Returns the txHgontemplateEventList. Keep in mind that the property is called "txHgontemplateEventList"
     * although it can hold several txHgontemplateEventList.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage An object storage containing the txHgontemplateEventList
     * @api
     */
    public function getTxHgontemplateEventList()
    {
        return $this->txHgontemplateEventList;
    }

    /**
     * Sets the txHgontemplateEventList. Keep in mind that the property is called "txHgontemplateEventList"
     * although it can hold several txHgontemplateEventList.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $txHgontemplateEventList
     * @return void
     * @api
     */
    public function setTxHgontemplateEventList(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $txHgontemplateEventList)
    {
        $this->txHgontemplateEventList = $txHgontemplateEventList;
    }

}