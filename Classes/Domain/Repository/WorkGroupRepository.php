<?php
namespace HGON\HgonTemplate\Domain\Repository;

/***
 *
 * This file is part of the "HGON WorkGroup" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2019 Maximilian Fäßler <maximilian@faesslerweb.de>, Fäßler Web UG
 *
 ***/

/**
 * The repository for WorkGroups
 */
class WorkGroupRepository extends \HGON\HgonWorkgroup\Domain\Repository\WorkGroupRepository
{
    /**
     * initializeObject
     *
     * @return void
     */
    public function initializeObject() {

        // @toDo: used as workaround (findAll does not work otherwise for Events filter)

        /** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
        $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');

        // don't add the pid constraint
        $querySettings->setRespectStoragePage(FALSE);
        $querySettings->setIgnoreEnableFields(TRUE);

        $this->setDefaultQuerySettings($querySettings);
    }
}
