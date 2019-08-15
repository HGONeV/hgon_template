<?php
namespace HGON\HgonTemplate\Controller;

/***
 *
 * This file is part of the "HGON Template" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2018 Maximilian Fäßler <maximilian@faesslerweb.de>, Fäßler Web UG
 *
 ***/
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use RKW\RkwEvents\Helper\DivUtility;
use \RKW\RkwBasics\Helper\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \HGON\HgonTemplate\Helper\Event as EventHelper;

/**
 * EventController
 */
class EventController extends \RKW\RkwEvents\Controller\EventController
{
    /**
     * eventRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\EventRepository
     * @inject
     */
    protected $eventRepository = null;

    /**
     * workGroupRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\WorkGroupRepository
     * @inject
     */
    protected $workGroupRepository = null;

    /**
     * documentTypeRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\DocumentTypeRepository
     * @inject
     */
    protected $documentTypeRepository = null;



    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $listItemsPerView = intval($this->settings['itemsPerPage']) ? intval($this->settings['itemsPerPage']) : 10;

        // first: Show standard evens
        parent::listAction();

        // Fix: The parent listAction pre-fills the filter with a "project" entry. This is disturbing the ajax call
        $this->view->assign('filter', []);

        // Additional to standard events: Get workgroup list
        $hgonWorkGroupSettings = self::getSettings('HgonWorkgroup', ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        // get event list with one additional counter (check for further elements)
        $workGroupEventList = $this->eventRepository->findNotFinishedOrderAsc($listItemsPerView + 1, $hgonWorkGroupSettings, true)->toArray();
        $showMoreLink = false;
        if ($listItemsPerView < count($workGroupEventList)) {
            $showMoreLink = true;
            // kill additional counter-item
            array_pop($workGroupEventList);
        }
        $this->view->assign('workGroupEventList', $workGroupEventList);
        // filter options
        $this->view->assign('documentTypeList', $this->documentTypeRepository->findAllByTypeAndVisibility('events', false));
        $this->view->assign('workGroupList', $this->workGroupRepository->findAll());
        $this->view->assign('timeArrayList', EventHelper::createMonthListArray());
        $this->view->assign('showMoreLink', $showMoreLink);
    }



    /**
     * action show
     *
     * @param \RKW\RkwEvents\Domain\Model\Event $event
     * @return void
     * @ignorevalidation $event
     */
    public function showAction(\RKW\RkwEvents\Domain\Model\Event $event)
    {
        // for list view within show
        $this->settings['itemsPerPage'] = 5;
        self::listAction();

        // prevent grouping
        $this->view->assign('noGrouping', true);
        $this->view->assign('eventToExclude', $event);

        // get standard show action
        parent::showAction($event);
    }



    /**
     * Returns TYPO3 settings
     *
     * @param string $extension extension name
     * @param string $which Which type of settings will be loaded
     * @return array
     */
    protected static function getSettings($extension = 'Rkwevents', $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTyposcriptConfiguration($extension, $which);
        //===
    }
}
