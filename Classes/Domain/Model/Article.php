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
 * Class Article
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Article extends \HGON\HgonPayment\Domain\Model\Article
{

    /**
     * txHgontemplateSubtitle
     *
     * @var string
     */
    protected $txHgontemplateSubtitle = '';

    /**
     * txHgontemplateImage
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $txHgontemplateImage = null;

    /**
     * txHgontemplateLink
     *
     * @var string
     */
    protected $txHgontemplateLink = '';


    /**
     * Returns the txHgontemplateSubtitle
     *
     * @return string $txHgontemplateSubtitle
     */
    public function getTxHgontemplateSubtitle()
    {
        return $this->txHgontemplateSubtitle;
    }

    /**
     * Sets the txHgontemplateSubtitle
     *
     * @param string $txHgontemplateSubtitle
     * @return void
     */
    public function setTxHgontemplateSubtitle($txHgontemplateSubtitle)
    {
        $this->txHgontemplateSubtitle = $txHgontemplateSubtitle;
    }

    /**
     * Returns the txHgontemplateImage
     *
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference $txHgontemplateImage
     */
    public function getTxHgontemplateImage()
    {
        return $this->txHgontemplateImage;
    }

    /**
     * Sets the txHgontemplateImage
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $txHgontemplateImage
     * @return void
     */
    public function setTxHgontemplateImage(\TYPO3\CMS\Extbase\Domain\Model\FileReference $txHgontemplateImage)
    {
        $this->txHgontemplateImage = $txHgontemplateImage;
    }

    /**
     * Returns the txHgontemplateLink
     *
     * @return string $txHgontemplateLink
     */
    public function getTxHgontemplateLink()
    {
        return $this->txHgontemplateLink;
    }

    /**
     * Sets the txHgontemplateLink
     *
     * @param string $txHgontemplateLink
     * @return void
     */
    public function setTxHgontemplateLink($txHgontemplateLink)
    {
        $this->txHgontemplateLink = $txHgontemplateLink;
    }


}
