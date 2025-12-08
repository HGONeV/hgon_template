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

use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * StandardController
 */
class StandardController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * @var \HGON\HgonTemplate\Domain\Repository\PagesRepository
     */
    protected $pagesRepository;

    /**
     * @var \HGON\HgonTemplate\Domain\Repository\AuthorsRepository
     */
    protected $authorsRepository;

    /**
     * @var \HGON\HgonTemplate\Domain\Repository\EventRepository
     */
    protected $eventRepository;

    /**
     * @var \HGON\HgonTemplate\Domain\Repository\NewsRepository
     */
    protected $newsRepository;

    /**
     * @var \HGON\HgonTemplate\Domain\Repository\SysCategoryRepository
     */
    protected $sysCategoryRepository;

    /**
     * @var \HGON\HgonTemplate\Domain\Repository\DidYouKnowRepository
     */
    protected $didYouKnowRepository;

    /**
     * @var \HGON\HgonTemplate\Domain\Repository\ProjectsRepository
     */
    protected $projectsRepository;

    /**
     * @var \HGON\HgonDonation\Domain\Repository\DonationRepository
     */
    protected $donationRepository;

    /**
     * @param \HGON\HgonTemplate\Domain\Repository\PagesRepository $pagesRepository
     */
    public function injectPagesRepository(\HGON\HgonTemplate\Domain\Repository\PagesRepository $pagesRepository): void {
        $this->pagesRepository = $pagesRepository;
    }

    /**
     * @param \HGON\HgonTemplate\Domain\Repository\AuthorsRepository $authorsRepository
     */
    public function injectAuthorsRepository(\HGON\HgonTemplate\Domain\Repository\AuthorsRepository $authorsRepository): void {
        $this->authorsRepository = $authorsRepository;
    }

    /**
     * @param \HGON\HgonTemplate\Domain\Repository\EventRepository $eventRepository
     */
    public function injectEventRepository(\HGON\HgonTemplate\Domain\Repository\EventRepository $eventRepository): void {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param \HGON\HgonTemplate\Domain\Repository\NewsRepository $newsRepository
     */
    public function injectNewsRepository(\HGON\HgonTemplate\Domain\Repository\NewsRepository $newsRepository): void {
        $this->newsRepository = $newsRepository;
    }

    /**
     * @param \HGON\HgonTemplate\Domain\Repository\SysCategoryRepository $sysCategoryRepository
     */
    public function injectSysCategoryRepository(\HGON\HgonTemplate\Domain\Repository\SysCategoryRepository $sysCategoryRepository): void {
        $this->sysCategoryRepository = $sysCategoryRepository;
    }

    /**
     * @param \HGON\HgonTemplate\Domain\Repository\DidYouKnowRepository $didYouKnowRepository
     */
    public function injectDidYouKnowRepository(\HGON\HgonTemplate\Domain\Repository\DidYouKnowRepository $didYouKnowRepository): void {
        $this->didYouKnowRepository = $didYouKnowRepository;
    }

    /**
     * @param \HGON\HgonTemplate\Domain\Repository\ProjectsRepository $projectsRepository
     */
    public function injectProjectsRepository(\HGON\HgonTemplate\Domain\Repository\ProjectsRepository $projectsRepository): void {
        $this->projectsRepository = $projectsRepository;
    }

    /**
     * @param \HGON\HgonDonation\Domain\Repository\DonationRepository $donationRepository
     */
    public function injectDonationRepository(\HGON\HgonDonation\Domain\Repository\DonationRepository $donationRepository): void {
        $this->donationRepository = $donationRepository;
    }

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
        $context = GeneralUtility::makeInstance(Context::class);
        $pageId  = (int)$context->getPropertyFromAspect('frontend.page', 'id');

        // RootlineUtility verwenden statt PageRepository::getRootLine()
        $rootlineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $pageId);
        $rootlinePages   = $rootlineUtility->get();

        // fo through all pages and take the one that has a match in the corresponsing field
        $pid = intval($GLOBALS['TSFE']->id);

        foreach ($rootlinePages as $page => $values) {
            if (
                ($values['tx_hgontemplate_contactperson'] > 0)
                && ($pid)
            ) {
                $pid = intval($values['uid']);
                break;
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
            $subPagesList = $this->pagesRepository->findByPid($siblingPages->getUid())->toArray();
            shuffle($subPagesList);
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



    /**
     * action projectPartner
     *
     *
     * @param \RKW\RkwProjects\Domain\Model\Projects $project
     * @return void
     */
    public function projectPartnerAction(\RKW\RkwProjects\Domain\Model\Projects $project = null)
    {
        // if set via flexform: Override in any case
        if ($this->settings['projectPartner']['projectUid']) {
            $projectUid = intval($this->settings['projectPartner']['projectUid']);
            $project = $this->projectsRepository->findByIdentifier($projectUid);
        }

        if (!$project) {
            $getParams = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_hgontemplate_project');
            $projectUid = preg_replace('/[^0-9]/', '', $getParams['project']);
            $project = $this->projectsRepository->findByIdentifier(intval($projectUid));
        }

        if (!$project) {
            $getParams = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_hgondonation_detail');

            if (key_exists('donation', $getParams)) {
                $donationUid = preg_replace('/[^0-9]/', '', $getParams['donation']);
            } else {
                // Workground in relation to FormExt: Although we got this params here, the GP vars above delivers some crap
                $donationUid = preg_replace('/[^0-9]/', '', $_GET['tx_hgondonation_detail']['donation']);
            }

            /** @var \HGON\HgonDonation\Domain\Model\Donation $donation */
            $donation = $this->donationRepository->findByIdentifier(intval($donationUid));
            $project = $donation->getTxRkwprojectProject();

        }

        $this->view->assign('project', $project);
    }



    /**
     * action authorList
     *
     * @return void
     */
    public function authorListAction()
    {
        $this->view->assign('authorList', $this->authorsRepository->findByUidList($this->settings['authorList']['authorUidList']));
    }
}
