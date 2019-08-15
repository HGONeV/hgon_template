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
 * Class Pages
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package RKW_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Pages extends \RKW\RkwBasics\Domain\Model\Pages
{

    /**
     * txRkwprojectsProjectUid
     *
     * @var \RKW\RkwProjects\Domain\Model\Projects
     */
    protected $txRkwprojectsProjectUid = null;

    /**
     * SysCategory
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HGON\HgonTemplate\Domain\Model\SysCategory>
     */
    protected $categories;

    /**
     * subPages
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HGON\HgonTemplate\Domain\Model\Pages>
     */
    protected $subPages;

    /**
     * txHgontemplateContactperson
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwAuthors\Domain\Model\Authors>
     */
    protected $txHgontemplateContactperson = null;

    /**
     * txHgontemplateArticle
     *
     * @var \HGON\HgonTemplate\Domain\Model\Article
     */
    protected $txHgontemplateArticle = null;


    /**
     * __construct
     */
    public function __construct()
    {
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
        $this->subPages = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->txHgontemplateContactperson = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Returns the txRkwprojectsProjectUid
     *
     * @return \RKW\RkwProjects\Domain\Model\Projects $txRkwprojectsProjectUid
     */
    public function getTxRkwprojectsProjectUid()
    {
        return $this->txRkwprojectsProjectUid;
    }

    /**
     * Sets the txRkwprojectsProjectUid
     *
     * @param \RKW\RkwProjects\Domain\Model\Projects $txRkwprojectsProjectUid
     * @return void
     */
    public function setTxRkwprojectsProjectUid(\RKW\RkwProjects\Domain\Model\Projects $txRkwprojectsProjectUid)
    {
        $this->txRkwprojectsProjectUid = $txRkwprojectsProjectUid;
    }


    /**
     * Adds a Category
     *
     * @param \HGON\HgonTemplate\Domain\Model\SysCategory $category
     * @return void
     */
    public function addCategory(\HGON\HgonTemplate\Domain\Model\SysCategory $category)
    {
        $this->categories->attach($category);
    }

    /**
     * Removes a Category
     *
     * @param \HGON\HgonTemplate\Domain\Model\SysCategory $categoryToRemove The Category to be removed
     * @return void
     */
    public function removeCategory(\HGON\HgonTemplate\Domain\Model\SysCategory $categoryToRemove)
    {
        $this->categories->detach($categoryToRemove);
    }

    /**
     * Returns the categories
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HGON\HgonTemplate\Domain\Model\SysCategory> $categories
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Sets the categories
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HGON\HgonTemplate\Domain\Model\SysCategory> $categories
     * @return void
     */
    public function setCategories(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories)
    {
        $this->categories = $categories;
    }

    /**
     * Adds a SubPages
     *
     * @param \HGON\HgonTemplate\Domain\Model\Pages $subPages
     * @return void
     */
    public function addSubPages(\HGON\HgonTemplate\Domain\Model\Pages $subPages)
    {
        // dirty hack: Constructor isn't called by any reason
        if (!$this->subPages instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage) {
            $this->initStorageObjects();
        }

        $this->subPages->attach($subPages);
    }

    /**
     * Removes a subPages
     *
     * @param \HGON\HgonTemplate\Domain\Model\Pages $subPagesToRemove The Pages to be removed
     * @return void
     */
    public function removeSubPages(\HGON\HgonTemplate\Domain\Model\Pages $subPagesToRemove)
    {
        $this->subPages->detach($subPagesToRemove);
    }

    /**
     * Returns the subPages
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HGON\HgonTemplate\Domain\Model\Pages> $subPages
     */
    public function getSubPages()
    {
        // dirty hack: Constructor isn't called by any reason
        if (!$this->subPages instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage) {
            $this->initStorageObjects();
        }

        return $this->subPages;
    }

    /**
     * Sets the subPages
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\HGON\HgonTemplate\Domain\Model\Pages> $subPages
     * @return void
     */
    public function setSubPages(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $subPages)
    {
        $this->subPages = $subPages;
    }

    /**
     * Adds a Authors
     *
     * @param \RKW\RkwAuthors\Domain\Model\Authors $txHgontemplateContactperson
     * @return void
     */
    public function addTxHgontemplateContactperson(\RKW\RkwAuthors\Domain\Model\Authors $txHgontemplateContactperson)
    {
        $this->txHgontemplateContactperson->attach($txHgontemplateContactperson);
    }

    /**
     * Removes a Authors
     *
     * @param \RKW\RkwAuthors\Domain\Model\Authors $txHgontemplateContactpersonToRemove The Authors to be removed
     * @return void
     */
    public function removeTxHgontemplateContactperson(\RKW\RkwAuthors\Domain\Model\Authors $txHgontemplateContactperson)
    {
        $this->txHgontemplateContactperson->detach($txHgontemplateContactperson);
    }

    /**
     * Returns the txHgontemplateContactperson
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwAuthors\Domain\Model\Authors> $txHgontemplateContactperson
     */
    public function getTxHgontemplateContactperson()
    {
        return $this->txHgontemplateContactperson;
    }

    /**
     * Sets the txHgontemplateContactperson
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwAuthors\Domain\Model\Authors> $txHgontemplateContactperson
     * @return void
     */
    public function setTxHgontemplateContactperson(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $txHgontemplateContactperson)
    {
        $this->txHgontemplateContactperson = $txHgontemplateContactperson;
    }

    /**
     * Returns the txHgontemplateArticle
     *
     * @return \HGON\HgonTemplate\Domain\Model\Article $txHgontemplateArticle
     */
    public function getTxHgontemplateArticle()
    {
        return $this->txHgontemplateArticle;
    }

    /**
     * Sets the txHgontemplateArticle
     *
     * @param \HGON\HgonTemplate\Domain\Model\Projects $txHgontemplateArticle
     * @return void
     */
    public function setTxHgontemplateArticle(\HGON\HgonTemplate\Domain\Model\Projects $txHgontemplateArticle)
    {
        $this->txHgontemplateArticle = $txHgontemplateArticle;
    }
}

?>