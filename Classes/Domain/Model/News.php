<?php

namespace HGON\HgonTemplate\Domain\Model;

use Mediadreams\MdNewsAuthor\Domain\Model\NewsAuthor;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

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
class News extends \GeorgRinger\News\Domain\Model\NewsDefault
{
    /**
     * @var ObjectStorage<NewsAuthor>
     */
    #[Lazy()]
    protected ObjectStorage $newsAuthor;

    public function __construct()
    {
        parent::__construct();
        $this->newsAuthor = new ObjectStorage();
    }

    /**
     * txHgontemplateYoutubeVideoId
     *
     * @var string
     */
    protected $txHgontemplateYoutubeVideoId = '';

    /**
     * txHgontemplateHeaderImage
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    #[Cascade(['value' => 'remove'])]
    protected $txHgontemplateHeaderImage = null;

    public function addNewsAuthor(NewsAuthor $newsAuthor): void
    {
        $this->newsAuthor ??= new ObjectStorage();
        $this->newsAuthor->attach($newsAuthor);
    }

    public function removeNewsAuthor(NewsAuthor $newsAuthorToRemove): void
    {
        $this->newsAuthor ??= new ObjectStorage();
        $this->newsAuthor->detach($newsAuthorToRemove);
    }

    public function getNewsAuthor(): ObjectStorage
    {
        $this->newsAuthor ??= new ObjectStorage();
        return $this->newsAuthor;
    }

    public function setNewsAuthor(ObjectStorage $newsAuthor): void
    {
        $this->newsAuthor = $newsAuthor;
    }

    /**
     * @return string
     */
    public function getTxHgontemplateYoutubeVideoId()
    {
        return $this->txHgontemplateYoutubeVideoId;
    }

    /**
     * @param string $txHgontemplateYoutubeVideoId
     */
    public function setTxHgontemplateYoutubeVideoId($txHgontemplateYoutubeVideoId)
    {
        $this->txHgontemplateYoutubeVideoId = $txHgontemplateYoutubeVideoId;
    }

    /**
     * Returns the txHgontemplateHeaderImage
     *
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference $txHgontemplateHeaderImage
     */
    public function getTxHgontemplateHeaderImage()
    {
        return $this->txHgontemplateHeaderImage;
    }

    /**
     * Sets the txHgontemplateHeaderImage
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $txHgontemplateHeaderImage
     * @return void
     */
    public function setTxHgontemplateHeaderImage(\TYPO3\CMS\Extbase\Domain\Model\FileReference $txHgontemplateHeaderImage)
    {
        $this->txHgontemplateHeaderImage = $txHgontemplateHeaderImage;
    }
}
