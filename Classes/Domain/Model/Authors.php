<?php

namespace HGON\HgonTemplate\Domain\Model;

use Mediadreams\MdNewsAuthor\Domain\Model\NewsAuthor;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

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
 * Class Authors
 *
 * @author Maximilian Fäßler <faesslerweb@web.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Authors extends NewsAuthor
{
    protected string $txHgontemplateShortDescription = '';

    protected string $txHgontemplateLongerDescription = '';

    protected string $phone2 = '';

    public function getFirstName(): string
    {
        return parent::getFirstname();
    }

    public function setFirstName(string $firstName): void
    {
        parent::setFirstname($firstName);
    }

    public function getLastName(): string
    {
        return parent::getLastname();
    }

    public function setLastName(string $lastName): void
    {
        parent::setLastname($lastName);
    }

    public function getFunctionTitle(): string
    {
        return parent::getPosition();
    }

    public function setFunctionTitle(string $functionTitle): void
    {
        parent::setPosition($functionTitle);
    }

    public function getFunctionDescription(): string
    {
        return parent::getBio();
    }

    public function setFunctionDescription(string $functionDescription): void
    {
        parent::setBio($functionDescription);
    }

    public function getImageBoxes(): ?FileReference
    {
        return parent::getImage();
    }

    public function setImageBoxes(?FileReference $imageBoxes): void
    {
        parent::setImage($imageBoxes);
    }

    /**
     * @return string
     */
    public function getTxHgontemplateShortDescription(): string
    {
        return $this->txHgontemplateShortDescription;
    }

    /**
     * @param string $txHgontemplateShortDescription
     */
    public function setTxHgontemplateShortDescription(string $txHgontemplateShortDescription): void
    {
        $this->txHgontemplateShortDescription = $txHgontemplateShortDescription;
    }

    /**
     * @return string
     */
    public function getTxHgontemplateLongerDescription(): string
    {
        return $this->txHgontemplateLongerDescription;
    }

    /**
     * @param string $txHgontemplateLongerDescription
     */
    public function setTxHgontemplateLongerDescription(string $txHgontemplateLongerDescription): void
    {
        $this->txHgontemplateLongerDescription = $txHgontemplateLongerDescription;
    }

    public function getPhone2(): string
    {
        return $this->phone2;
    }

    public function setPhone2(string $phone2): void
    {
        $this->phone2 = $phone2;
    }
}
