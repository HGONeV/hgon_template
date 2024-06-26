<?php

namespace HGON\HgonTemplate\Domain\Repository;
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
 * NewsletterRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class NewsletterRepository extends \RKW\RkwNewsletter\Domain\Repository\NewsletterRepository
{

    protected $defaultOrderings = array(
        'txHgontemplateNewsList' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
    );

    /**
     * get news of a newsletter configuration. Solves sorting issue
     * (we can't get the object storage sorting itself. The records are always sorted by uid)
     *
     * @param \HGON\HgonTemplate\Domain\Model\Newsletter $newsletter
     * @return \Traversable
    *
    public function findNewsOfNewsletter(\HGON\HgonTemplate\Domain\Model\Newsletter $newsletter)
    {

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching(
            $query->equals('uid', $newsletter)
        );

        $newsletter = $query->execute()->getFirst();

        return $query->execute()->getFirst();
        //===
    }*/


}