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

/**
 * StandardController
 */
class StandardController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * pagesRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\PagesRepository
     * @inject
     */
    protected $pagesRepository = null;

    /**
     * authorsRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\AuthorsRepository
     * @inject
     */
    protected $authorsRepository = null;

    /**
     * eventRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\EventRepository
     * @inject
     */
    protected $eventRepository = null;

    /**
     * newsRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\NewsRepository
     * @inject
     */
    protected $newsRepository = null;

    /**
     * sysCategoryRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\SysCategoryRepository
     * @inject
     */
    protected $sysCategoryRepository = null;

    /**
     * didYouKnowRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\DidYouKnowRepository
     * @inject
     */
    protected $didYouKnowRepository = null;

    /**
     * cacheManager
     *
     * @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend
     */
    protected $cacheManager;

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected $cObj;


    public function initializeAction()
    {
      //  $this->cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache("hgon_template");
      //  $this->cObj = $this->configurationManager->getContentObject();
    }


    /**
     * action pageHighlight
     *
     * @return void
     */
    public function pageHighlightAction()
    {
        $this->view->assign('pages', $this->pagesRepository->findByIdentifier(intval($this->settings['pageHighlight']['pid'])));
        $this->view->assign('subPagesList', $this->pagesRepository->findByPid(intval($this->settings['pageHighlight']['pid'])));
    }



    /**
     * action randomAuthor
     * shows a random author (from RkwAuthors)
     *
     * @return void
     */
    public function randomAuthorAction()
    {
        //$authorsList = $this->authorsRepository->findAll();
        $authorsList = $this->authorsRepository->findByUidList($this->settings['randomAuthor']['authorUidList']);
        $this->view->assign('author', $authorsList[rand(0, count($authorsList) - 1)]);
    }



    /**
     * action projectTeaser
     *
     * @return void
     */ /*
    public function projectTeaserAction()
    {
        /*
        // if shuffle, just select three entries
        if ($this->settings['projectTeaser']['random']) {
            $projectList = $this->projectsRepository->findAll()->toArray();
            shuffle($projectList);
            $projectList = array_slice($projectList, 0, 3, true);
        } else {
            $projectList = $this->projectsRepository->findByUidList($this->settings['projectTeaser']['projectUidList']);
        }

        // ugly function, because we don't have pages objects (we got typolinks)
        /** @var \HGON\HgonTemplate\Domain\Model\Projects $project
        foreach ($projectList as $project) {
            if ($project->getProjectPid()) {
                $explodedLink = GeneralUtility::trimExplode('=', $project->getProjectPid());
                $project->setPages($this->pagesRepository->findByIdentifier(intval(end($explodedLink))));
            }
        }

        $this->view->assign('projectList', $projectList);

    }*/



    /**
     * Shows author which is contact person of the current project
     *
     * @return void
    */
    public function sidebarContactPersonAction()
    {
        // get PageRepository and rootline
        $repository = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
        $rootlinePages = $repository->getRootLine(intval($GLOBALS['TSFE']->id));

        // fo through all pages and take the one that has a match in the corresponsing field
        $pid = intval($GLOBALS['TSFE']->id);

        foreach ($rootlinePages as $page => $values) {
            if (
                ($values['tx_hgontemplate_contactperson'] > 0)
                && ($pid)
            ) {
                $pid = intval($values['uid']);
                break;
                //===
            }
        }

        $result = $this->pagesRepository->findByUid($pid);

        if ($result instanceof \HGON\HgonTemplate\Domain\Model\Pages) {
            if ($result->getTxHgontemplateContactperson()){
                foreach ($result->getTxHgontemplateContactperson() as $author) {
                    $this->view->assign('author', $author);
                }
            }
        }
    }



    /**
     * Shows a project overview of sibling pages
     *
     * @return void
     */
    public function siblingPagesOverviewAction()
    {
        /** @var \HGON\HgonTemplate\Domain\Model\Pages $currentPages */
        $currentPages = $this->pagesRepository->findByIdentifier(intval($GLOBALS['TSFE']->id));

        // Get sibling pages of current PageUid
        $siblingPagesList = $this->pagesRepository->findByPagesExcludeCurrent($currentPages);

        // Get (direct) sub-pages of this siblings -> delivers NOT the whole pagetree!
        /** @var \HGON\HgonTemplate\Domain\Model\Pages $siblingPages */
        foreach ($siblingPagesList as $siblingPages) {
            $subPagesList = $this->pagesRepository->findByPid($siblingPages->getUid());
            foreach ($subPagesList as $subPages) {
                $siblingPages->addSubPages($subPages);
            }
        }

        // Return Sibling-Pages with subPages to view
        $this->view->assign('pagesList', $siblingPagesList);
    }



    /**
     * Shows a project overview of children pages
     *
     * @return void
     */
    public function childrenPagesOverviewAction()
    {
        /** @var \HGON\HgonTemplate\Domain\Model\Pages $currentPages */
        $currentPages = $this->pagesRepository->findByIdentifier(intval($GLOBALS['TSFE']->id));

        $childrenPagesList = [];

        $subPagesList = $this->pagesRepository->findByPid($currentPages->getUid());
        foreach ($subPagesList as $subPages) {
            $childrenPagesList[] = $subPages;
        }

        // Return Sibling-Pages with subPages to view
        $this->view->assign('pagesList', $childrenPagesList);
    }



    /**
     * action pageSlider
     *
     * @return void
     */
    public function pageSliderAction()
    {
        $this->view->assign('pagesList', $this->pagesRepository->findByUidList($this->settings['pageSlider']['pidList']));
    }



    /**
     * action donationForm
     * -> TESTING: Shows a "Fundraising form"
     *
     * @deprecated Not used.
     * @return void

    public function donationFormAction()
    {
        // @TESTING: Shows a "Fundraising form"
        // do nothing else: Show JS in Template
    }*/



    /**
     * action supportOptions
     * - become a member form
     * - donate money
     * - donate time
     * -> Options are defined via flexForm
     *
     * @deprecated Moved to HGON DONATION
     * @return void

    public function supportOptionsAction()
    {
        // do nothing else
    }*/



    /**
     * action supportOptionsLight
     * -> Gives no real forms. Only anchors for opening forms of "supportOptions" plugin, which is used as standard footer element
     *
     * @deprecated Moved to HGON DONATION
     * @return void

    public function supportOptionsLightAction()
    {
        // do nothing else
    }*/



    /**
     * action sixReasons
     *
     * @return void
     */
    public function sixReasonsAction()
    {
        // do nothing else (output of flexform content)
    }



    /**
     * action didYouKnow
     *
     *
     * @param \HGON\HgonTemplate\Domain\Model\SysCategory $sysCategory
     * @return void
     */
    public function didYouKnowAction(\HGON\HgonTemplate\Domain\Model\SysCategory $sysCategory = null)
    {
        if (!$sysCategory) {
            $getParams = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_hgontemplate_journal');
            $sysCategoryUid = preg_replace('/[^0-9]/', '', $getParams['sysCategory']);
            $sysCategory = $this->sysCategoryRepository->findByIdentifier($sysCategoryUid);
        }

        // add "didYouKnow" random (check for category-entry. Else take something)
        $didYouKnowListByCategory = $this->didYouKnowRepository->findBySysCategory($sysCategory);
        if ($didYouKnowListByCategory->count()) {
            $didYouKnowList = $didYouKnowListByCategory;
        } else {
            // fallback: FindAll (if no category is given)
            $didYouKnowList = $this->didYouKnowRepository->findAll();
        }
        $this->view->assign('didYouKnow', $didYouKnowList[rand(0, count($didYouKnowList) - 1)]);
    }



    /**
     * action maps
     *
     * @return void
     */
    public function mapsAction()
    {
        // showMapsPidList (if current PID is registered in $this->settings['showMapsPidList'])
        $this->view->assign('showMaps', in_array(intval($GLOBALS['TSFE']->id), GeneralUtility::trimExplode(',', $this->settings['showMapsPidList'])));
    }
}
