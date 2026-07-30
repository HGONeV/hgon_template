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

    protected \HGON\HgonTemplate\Domain\Repository\DidYouKnowRepository $didYouKnowRepository;

    public function __construct(
        ?\HGON\HgonTemplate\Domain\Repository\PagesRepository $pagesRepository = null,
        ?\HGON\HgonTemplate\Domain\Repository\AuthorsRepository $authorsRepository = null,
        ?\HGON\HgonTemplate\Domain\Repository\NewsRepository $newsRepository = null,
        ?\HGON\HgonTemplate\Domain\Repository\DidYouKnowRepository $didYouKnowRepository = null
    ) {
        $this->pagesRepository = $pagesRepository ?? GeneralUtility::makeInstance(\HGON\HgonTemplate\Domain\Repository\PagesRepository::class);
        $this->authorsRepository = $authorsRepository ?? GeneralUtility::makeInstance(\HGON\HgonTemplate\Domain\Repository\AuthorsRepository::class);
        $this->newsRepository = $newsRepository ?? GeneralUtility::makeInstance(\HGON\HgonTemplate\Domain\Repository\NewsRepository::class);
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
     * shows a random author
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function randomAuthorAction()
    {
        //$authorsList = $this->authorsRepository->findAll();
        $authorsList = array_values(array_filter(
            $this->authorsRepository->findByUidList($this->settings['randomAuthor']['authorUidList'] ?? ''),
            static function ($author): bool {
                $description = trim(strip_tags(html_entity_decode((string)$author->getFunctionDescription(), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                return $description !== '';
            }
        ));

        $this->view->assign('author', $authorsList !== [] ? $authorsList[random_int(0, count($authorsList) - 1)] : null);

        return $this->htmlResponse();
    }

    /**
     * Shows author which is contact person of the current project
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sidebarContactPersonAction()
    {
        $pageInformation = $this->request->getAttribute('frontend.page.information');
        $rootLine = $pageInformation?->getRootLine() ?? [];

        // TYPO3 provides the rootline from the current page towards the site root.
        // Therefore the first configured contact person is also the nearest one.
        foreach ($rootLine as $page) {
            $authorUid = (int)($page['tx_hgontemplate_contactperson'] ?? 0);
            if ($authorUid <= 0) {
                continue;
            }

            $author = $this->authorsRepository->findByUid($authorUid);
            if ($author instanceof \HGON\HgonTemplate\Domain\Model\Authors) {
                $this->view->assign('author', $author);
                break;
            }
        }

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
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function didYouKnowAction()
    {
        $didYouKnowList = $this->didYouKnowRepository->findAll();
        if ($didYouKnowList->count() > 0) {
            $this->view->assign(
                'didYouKnow',
                $didYouKnowList[random_int(0, $didYouKnowList->count() - 1)]
            );
        }

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
