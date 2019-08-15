<?php

namespace HGON\HgonTemplate\Domain\Model\FormElements;

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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Frontend\Category\Collection\CategoryCollection;

/**
 * Class ArticleOptions
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ArticleOptions extends GenericFormElement
{
    public function setProperty(string $key, $value)
    {
       // if ($key === 'articleUid') {
            $this->setProperty('options', $this->getOptions());
            //return;
       // }

     //   parent::setProperty($key, $value);
    }

    protected function getOptions() : array
    {
        $options = [];
        foreach ($this->getArticle() as $article) {
            $options[$article['uid']] = $article['title'];
        }
        asort($options);
        return $options;
    }

    protected function getArticle() : array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_hgontemplate_domain_model_article');
        $queryBuilder->setRestrictions(
            GeneralUtility::makeInstance(FrontendRestrictionContainer::class)
        );

        return $queryBuilder
            ->select('uid', 'title')
            ->from('tx_hgontemplate_domain_model_article')
            ->execute()
            ->fetchAll();
    }
}