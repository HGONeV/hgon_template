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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * StandardController
 */
class StandardController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    protected \HGON\HgonTemplate\Domain\Repository\PagesRepository $pagesRepository;

    protected \HGON\HgonTemplate\Domain\Repository\AuthorsRepository $authorsRepository;

    protected \HGON\HgonTemplate\Domain\Repository\NewsRepository $newsRepository;

    protected \HGON\HgonTemplate\Domain\Repository\SysCategoryRepository $sysCategoryRepository;

    protected \HGON\HgonTemplate\Domain\Repository\DidYouKnowRepository $didYouKnowRepository;

    public function __construct(
        ?\HGON\HgonTemplate\Domain\Repository\PagesRepository $pagesRepository = null,
        ?\HGON\HgonTemplate\Domain\Repository\AuthorsRepository $authorsRepository = null,
        ?\HGON\HgonTemplate\Domain\Repository\NewsRepository $newsRepository = null,
        ?\HGON\HgonTemplate\Domain\Repository\SysCategoryRepository $sysCategoryRepository = null,
        ?\HGON\HgonTemplate\Domain\Repository\DidYouKnowRepository $didYouKnowRepository = null
    ) {
        $this->pagesRepository = $pagesRepository ?? GeneralUtility::makeInstance(\HGON\HgonTemplate\Domain\Repository\PagesRepository::class);
        $this->authorsRepository = $authorsRepository ?? GeneralUtility::makeInstance(\HGON\HgonTemplate\Domain\Repository\AuthorsRepository::class);
        $this->newsRepository = $newsRepository ?? GeneralUtility::makeInstance(\HGON\HgonTemplate\Domain\Repository\NewsRepository::class);
        $this->sysCategoryRepository = $sysCategoryRepository ?? GeneralUtility::makeInstance(\HGON\HgonTemplate\Domain\Repository\SysCategoryRepository::class);
        $this->didYouKnowRepository = $didYouKnowRepository ?? GeneralUtility::makeInstance(\HGON\HgonTemplate\Domain\Repository\DidYouKnowRepository::class);
    }

    public function injectPagesRepository(\HGON\HgonTemplate\Domain\Repository\PagesRepository $pagesRepository): void
    {
        $this->pagesRepository = $pagesRepository;
    }

    public function injectAuthorsRepository(\HGON\HgonTemplate\Domain\Repository\AuthorsRepository $authorsRepository): void
    {
        $this->authorsRepository = $authorsRepository;
    }

    public function injectNewsRepository(\HGON\HgonTemplate\Domain\Repository\NewsRepository $newsRepository): void
    {
        $this->newsRepository = $newsRepository;
    }

    public function injectSysCategoryRepository(\HGON\HgonTemplate\Domain\Repository\SysCategoryRepository $sysCategoryRepository): void
    {
        $this->sysCategoryRepository = $sysCategoryRepository;
    }

    public function injectDidYouKnowRepository(\HGON\HgonTemplate\Domain\Repository\DidYouKnowRepository $didYouKnowRepository): void
    {
        $this->didYouKnowRepository = $didYouKnowRepository;
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
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function pageHighlightAction()
    {
        $this->view->assign('pages', $this->pagesRepository->findByIdentifier(intval($this->settings['pageHighlight']['pid'])));
        $this->view->assign('subPagesList', $this->pagesRepository->findByPid(intval($this->settings['pageHighlight']['pid'])));

        return $this->htmlResponse();
    }



    /**
     * action randomAuthor
     * shows a random author (from RkwAuthors)
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function randomAuthorAction()
    {
        //$authorsList = $this->authorsRepository->findAll();
        $authorsList = $this->authorsRepository->findByUidList($this->settings['randomAuthor']['authorUidList']);
        $this->view->assign('author', $authorsList[rand(0, count($authorsList) - 1)]);

        return $this->htmlResponse();
    }

    /**
     * Shows author which is contact person of the current project
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sidebarContactPersonAction()
    {
        // erst ab v10 kompatibel
        /*
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
        */

        return $this->htmlResponse();
    }



    /**
     * Shows a project overview of sibling pages
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function siblingPagesOverviewAction()
    {
        /** @var \HGON\HgonTemplate\Domain\Model\Pages $currentPages */
        $currentPages = $this->pagesRepository->findByIdentifier(
            (int)($this->request->getAttribute('frontend.page.information')?->getId() ?? 0)
        );

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

        return $this->htmlResponse();
    }



    /**
     * Shows a project overview of children pages
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function childrenPagesOverviewAction()
    {
        /** @var \HGON\HgonTemplate\Domain\Model\Pages $currentPages */
        $currentPages = $this->pagesRepository->findByIdentifier(
            (int)($this->request->getAttribute('frontend.page.information')?->getId() ?? 0)
        );

        $childrenPagesList = [];

        $subPagesList = $this->pagesRepository->findByPid($currentPages->getUid());

        foreach ($subPagesList as $subPages) {
            $childrenPagesList[] = $subPages;
        }

        // Return Sibling-Pages with subPages to view
        $this->view->assign('pagesList', $childrenPagesList);

        return $this->htmlResponse();
    }



    /**
     * action pageSlider
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function pageSliderAction()
    {
        $this->view->assign('pagesList', $this->pagesRepository->findByUidList($this->settings['pageSlider']['pidList']));

        return $this->htmlResponse();
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
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sixReasonsAction()
    {
        // do nothing else (output of flexform content)

        return $this->htmlResponse();
    }



    /**
     * action didYouKnow
     *
     *
     * @param \HGON\HgonTemplate\Domain\Model\SysCategory $sysCategory
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function didYouKnowAction(?\HGON\HgonTemplate\Domain\Model\SysCategory $sysCategory = null)
    {
        if (!$sysCategory) {
            $getParams = $this->request->getQueryParams()['tx_hgontemplate_journal'] ?? [];
            $sysCategoryParam = (string)($getParams['sysCategory'] ?? '');
            $sysCategoryUid = (int)preg_replace('/\D+/', '', $sysCategoryParam);

            if ($sysCategoryUid > 0) {
                $sysCategory = $this->sysCategoryRepository->findByIdentifier($sysCategoryUid);
            }
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

        return $this->htmlResponse();
    }



    /**
     * action maps
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function mapsAction()
    {
        // showMapsPidList (if current PID is registered in $this->settings['showMapsPidList'])
        $pageId = (int)($this->request
            ->getAttribute('frontend.page.information')
            ?->getId() ?? 0);

        $this->view->assign(
            'showMaps',
            in_array(
                $pageId,
                GeneralUtility::intExplode(',', (string)($this->settings['showMapsPidList'] ?? '')),
                true
            )
        );

        return $this->htmlResponse();
    }

    /**
     * action authorList
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function authorListAction()
    {
        $this->view->assign('authorList', $this->authorsRepository->findByUidList($this->settings['authorList']['authorUidList']));

        return $this->htmlResponse();
    }
}
