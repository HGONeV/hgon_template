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

use \HGON\HgonTemplate\Utility\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
/**
 * NewsController
 */
class NewsController extends \GeorgRinger\News\Controller\NewsController
{
    /**
     * @var \HGON\HgonTemplate\Domain\Repository\PagesRepository
     */
    protected $pagesRepository;

    /**
     *  \HGON\HgonTemplate\Domain\Repository\NewsRepository
     */
    // Intentionally not redeclared to keep compatibility with parent property.

    /**
     * @var \HGON\HgonTemplate\Domain\Repository\SysCategoryRepository
     */
    protected $sysCategoryRepository;

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
     *  \HGON\HgonTemplate\Domain\Repository\NewsRepository $newsRepository
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
     * @param \HGON\HgonDonation\Domain\Repository\DonationRepository $donationRepository
     */
    public function injectDonationRepository(\HGON\HgonDonation\Domain\Repository\DonationRepository $donationRepository): void {
        $this->donationRepository = $donationRepository;
    }

    /**
     * showRelatedSidebarAction
     */
    public function showRelatedSidebarAction()
    {
        $categories = [];
        $newsToExclude = [];

        // Get category of news, if news detail
        $qp = $this->request->getQueryParams();

        $request = $qp['tx_news_pi1'] ?? null;
        if (is_array($request) && !empty($request['news'])) {
            $newsUid = (int)$request['news'];

            $news = $this->newsRepository->findByIdentifier($newsUid);
            if ($news && $news->getCategories()->count() > 0) {
                $categories = $news->getCategories();
            }

            // except current news
            if ($news) {
                $newsToExclude[] = $news;
            }
        }

        // Else: on donation detail: Use Project category
        if (!$categories) {
            $getParams = $qp['tx_hgondonation_detail'] ?? [];
            $getParams = is_array($getParams) ? $getParams : [];

            $donationUid = (int)preg_replace('/\D+/', '', (string)($getParams['donation'] ?? ''));

            /** @var \HGON\HgonDonation\Domain\Model\Donation|null $donation */
            $donation = $donationUid > 0 ? $this->donationRepository->findByIdentifier($donationUid) : null;

            if ($donation?->getTxRkwprojectProject()?->getSysCategory()?->count() > 0) {
                $categories = $donation->getTxRkwprojectProject()->getSysCategory();
            }
        }


        // Else: Get categories of pages
        if (!$categories) {
            /** @var \HGON\HgonTemplate\Domain\Model\Pages $pages */
            $pages = $this->pagesRepository->findByIdentifier(
                (int)($this->request->getAttribute('frontend.page.information')?->getId() ?? 0)
            );
            if (count($pages->getCategories())) {
                $categories = $pages->getCategories();
            }
        }


        if ($categories) {
            $this->view->assign('newsList', $this->newsRepository->findByCategories($categories, $newsToExclude));
        } else {
            // fallback - get five current news
            $this->view->assign('newsList', $this->newsRepository->findAll()->getQuery()->setLimit(5)->execute());
        }
        $this->view->assign('settingsHgonTemplate', self::getSettings('HgonTemplate'));

        return $this->htmlResponse();
    }



    /**
     * action journalHighlight
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function journalHighlightAction()
    {
        // if set: Show manual choosen news first
        $mainNews = $this->newsRepository->findByIdentifier(intval($this->settings['journalHighlight']['newsUid']));
        $this->view->assign('newsManualSelect', $mainNews);
        $this->view->assign('newsList', $this->newsRepository->findAllExceptCurrent($mainNews));

        return $this->htmlResponse();
    }



    /**
     * action journal
     *
     *
     * @param \HGON\HgonTemplate\Domain\Model\SysCategory $sysCategory
     * @param integer $pageNumber
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function journalAction(?\HGON\HgonTemplate\Domain\Model\SysCategory $sysCategory = null, $pageNumber = 0)
    {
        // workaround for easy use on some further pages:
        // If it's not the journal page, try to grab the pages categories und show related news
        // Except a sysCategory is already set (pagination)
        $isJournalPage = true;
        $pageId = (int)($this->request
            ->getAttribute('frontend.page.information')
            ?->getId() ?? 0);

        if (
            (int)($this->settings['journal']['pageUid'] ?? 0) !== $pageId
            && !$sysCategory
        ) {
            $isJournalPage = false;

            /** @var \HGON\HgonTemplate\Domain\Model\Pages|null $pages */
            $pages = $this->pagesRepository->findByIdentifier($pageId);
            if ($pages && $pages->getCategories()->count() > 0) {
                // aktuell nur eine Kategorie
                $sysCategory = $pages->getCategories()->current();
            }
        }
        $templateDataArray['isJournalPage'] = $isJournalPage;

        $pageNumber++;
        $templateDataArray = [];

        /** @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository $frontendUserRepository */
     //   $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var \HGON\HgonTemplate\Helper\Journal $journalHelper */
     //   $journalHelper = $objectManager->get('HGON\\HgonTemplate\\Helper\\Journal');

        // a) Entweder: Völlig dynamisch
        //    $templateDataArray['journalRowList'] = $journalHelper->createResultSet($this->settings, $pageNumber, $sysCategory);

        // b) Oder: Einfach
     //   $helperRequest = $journalHelper->getPagesList($this->settings, $pageNumber, $sysCategory);
        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $pagesWithCategoryList */
      //  $pagesWithCategoryList = $helperRequest['sysCategoryList'];
      //  $templateDataArray['journalRowList'] = $helperRequest['journalRowList'];
        // @toDo: If $templateDataArray['journalRowList'] delivers only a few results: Try to fill with news

        $templateDataArray['journalRowList'] = $this->newsRepository->findByFilter($sysCategory, [], [], $pageNumber, intval($this->settings['journal']['itemsPerPage']));

        // sysCategory is set, if user is filtering
        /*
        if ($sysCategory) {
            $journalParentCategory = $sysCategory;
        } else {
            $journalParentCategory = $this->sysCategoryRepository->findByIdentifier($this->settings['journal']['parentCategoryUid']);
        }

        // category list (find by "journal"-sysCategory, or given sysCategory)
        $sysCategoryList = $this->sysCategoryRepository->findOneWithAllRecursiveChildren($journalParentCategory, null, true);

        // pages list
        $pagesWithCategoryList = $this->pagesRepository->findBySysCategory($sysCategoryList);
        if ($pagesWithCategoryList->count()) {
            $templateDataArray['journalRowList'] = $this->pagesRepository->findTreeByParentPages($pagesWithCategoryList, true, $pageNumber, intval($this->settings['journal']['itemsPerPage']));
        }
        */

        // get categories of news
        if ($isJournalPage) {
            $newsList = $this->newsRepository->findAll();
            $categoryList = [];
            /** @var \HGON\HgonTemplate\Domain\Model\News $news */
            foreach ($newsList as $news) {
                /** @var \HGON\HgonTemplate\Domain\Model\SysCategory $category */
                foreach ($news->getCategories() as $category) {

                    // some logic: Show only selected category and children (if a category is selected)
                    if (
                        // get all top categories (OR) categories and their direct children
                        (
                            !$sysCategory
                            && $category->getParentcategory()->getUid() == $this->settings['journal']['parentCategoryUid']
                        )
                        ||
                        ($sysCategory
                        && (
                            $category->getUid() == $sysCategory->getUid()
                            || $category->getParentcategory()->getUid() == $sysCategory->getUid()
                        ))
                    ) {
                        $categoryList[$category->getUid()] = $category;
                    }

                }
            }
            $templateDataArray['sysCategoryList'] = $categoryList;
        }

        $templateDataArray['selectedSysCategory'] = $sysCategory;
        $templateDataArray['pageTypeAjax'] = $this->settings['journal']['ajaxTypeNum'];
        $templateDataArray['pageNumber'] = $pageNumber;


        if (count($templateDataArray['journalRowList'])) {
            $templateDataArray['showMoreLink'] = $this->newsRepository->findByFilter($sysCategory, [], [], 1, 9999)->count() > $pageNumber * intval($this->settings['journal']['itemsPerPage']) ? true : false;
        } else {
            $templateDataArray['showMoreLink'] = false;
        }

        // check for ajax context
        // WICHTIG: Aktuell findet nur bei der Paginierung ein Ajax-Request statt. NICHT bei der Kategorieauswahl!
        // -> Weil die Kategorie Teil der URL sein soll
        $ajaxTypeNum = (int)($this->settings['journal']['ajaxTypeNum'] ?? 0);
        // "type" kommt bei TYPO3 als Query-Parameter (?type=123) rein:
        $type = (int)($this->request->getQueryParams()['type'] ?? 0);
        if ($type === $ajaxTypeNum) {

            // get JSON helper
            /** @var \RKW\RkwBasics\Helper\Json $jsonHelper */
            $jsonHelper = GeneralUtility::makeInstance(\RKW\RkwBasics\Helper\Json::class);

            // ajax context: Set settings manually
            $templateDataArray['settings'] = $this->settings;

            // if sysCategory and page 1 - reload whole content. Else: Append
            $kindOfRequest = $pageNumber == 1 ? 'replace' : 'append';

            // Content
            $jsonHelper->setHtml(
                'journal-flex-container',
                $templateDataArray,
                $kindOfRequest,
                'Ajax/Journal/' . ucfirst($kindOfRequest) . '.html'
            );

            // More link replace
            $jsonHelper->setHtml(
                'journal-more-link-container',
                $templateDataArray,
                'replace',
                'Ajax/Journal/MoreLink.html'
            );

            print (string)$jsonHelper;
            exit();

        } else {
            $this->view->assignMultiple($templateDataArray);
        }

        return $this->htmlResponse();

    }



    /**
     * action header
     * Template helper
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function headerAction()
    {
        $getParams = $this->request->getQueryParams()['tx_news_pi1'] ?? [];

        $newsUid = (int)preg_replace('/\D+/', '', (string)($getParams['news'] ?? ''));
        if ($newsUid <= 0) {
            return $this->htmlResponse();
        }

        $news = $this->newsRepository->findByIdentifier($newsUid);
        if ($news !== null) {
            $this->view->assign('newsItem', $news);
        }

        return $this->htmlResponse();
    }



    /**
     * action sidebar
     * Template helper
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sidebarAction()
    {

        $getParams = $this->request->getQueryParams()['tx_news_pi1'] ?? null;

        $newsUid = (int)preg_replace('/\D+/', '', (string)($getParams['news'] ?? ''));
        $news = $this->newsRepository->findByIdentifier(filter_var($newsUid, FILTER_SANITIZE_NUMBER_INT));

        if ($news) {
            $this->view->assign('newsItem', $news);
            //$this->view->assign('newsList', $this->newsRepository->findByFilter([], [$workGroup]));
        }

        return $this->htmlResponse();
    }



    /**
     * Returns TYPO3 settings
     *
     * @param string $extension extension name
     * @param string $which Which type of settings will be loaded
     * @return array
     */
    protected static function getSettings($extension = 'News', $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTyposcriptConfiguration($extension, $which);
    }

}
