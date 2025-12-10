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
 * Class Authors
 *
 * @author Maximilian Fäßler <faesslerweb@web.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Authors extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * txHgontemplateShortDescription
     *
     * @var string
     */
    protected $txHgontemplateShortDescription = '';

    /**
     * txHgontemplateLongerDescription
     *
     * @var string
     */
    protected $txHgontemplateLongerDescription = '';

    /**
     * @return string
     */
    public function getTxHgontemplateShortDescription()
    {
        return $this->txHgontemplateShortDescription;
    }

    /**
     * @param string $txHgontemplateShortDescription
     */
    public function setTxHgontemplateShortDescription($txHgontemplateShortDescription)
    {
        $this->txHgontemplateShortDescription = $txHgontemplateShortDescription;
    }

    /**
     * @return string
     */
    public function getTxHgontemplateLongerDescription()
    {
        return $this->txHgontemplateLongerDescription;
    }

    /**
     * @param string $txHgontemplateLongerDescription
     */
    public function setTxHgontemplateLongerDescription($txHgontemplateLongerDescription)
    {
        $this->txHgontemplateLongerDescription = $txHgontemplateLongerDescription;
    }
}
