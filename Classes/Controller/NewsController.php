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
use HGON\HgonTemplate\Utility\AjaxResponseBuilder;

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
    public function journalAction(
        ?\HGON\HgonTemplate\Domain\Model\SysCategory $sysCategory = null,
        $pageNumber = 0,
        string $searchTerm = '',
        string $dateRange = 'all',
        int $primaryCategory = 0,
        int $secondaryCategory = 0
    )
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
        $pageNumber++;
        $templateDataArray = [];
        $templateDataArray['isJournalPage'] = $isJournalPage;
        $searchTerm = trim($searchTerm);
        $allowedDateRanges = ['all', 'last12', 'last36', 'older36'];
        if (!in_array($dateRange, $allowedDateRanges, true)) {
            $dateRange = 'all';
        }

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

        $newsList = $this->newsRepository->findAll();
        $categoryOptions = $this->buildJournalCategoryOptions(
            $newsList,
            (int)$this->settings['journal']['parentCategoryUid']
        );

        if ($sysCategory instanceof \HGON\HgonTemplate\Domain\Model\SysCategory) {
            $selectedCategoryUid = (int)$sysCategory->getUid();
            $selectedParentUid = (int)($sysCategory->getParentcategory()?->getUid() ?? 0);

            if ($selectedParentUid === (int)$this->settings['journal']['parentCategoryUid']) {
                $primaryCategory = $selectedCategoryUid;
            } elseif ($selectedParentUid > 0) {
                $secondaryCategory = $selectedCategoryUid;
                $primaryCategory = $selectedParentUid;
            }
        }

        $categoryFilterList = $this->resolveJournalCategoryFilterList(
            $primaryCategory,
            $secondaryCategory,
            $sysCategory,
            $categoryOptions['secondaryCategoriesByParent'],
            (int)$this->settings['journal']['parentCategoryUid']
        );

        $activeSecondaryCategories = $primaryCategory > 0 && isset($categoryOptions['secondaryCategoriesByParent'][$primaryCategory])
            ? $categoryOptions['secondaryCategoriesByParent'][$primaryCategory]
            : $categoryOptions['allSecondaryCategories'];

        $filters = [
            'searchTerm' => $searchTerm,
            'dateRange' => $dateRange,
        ];

        $itemsPerPage = intval($this->settings['journal']['itemsPerPage']);
        $templateDataArray['journalRowList'] = $this->newsRepository->findByFilter(
            $categoryFilterList,
            [],
            [],
            $pageNumber,
            $itemsPerPage,
            $filters
        );

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
        $templateDataArray['selectedSearchTerm'] = $searchTerm;
        $templateDataArray['selectedDateRange'] = $dateRange;
        $templateDataArray['selectedPrimaryCategory'] = $primaryCategory;
        $templateDataArray['selectedSecondaryCategory'] = $secondaryCategory;
        $templateDataArray['primaryCategoryOptions'] = $categoryOptions['topCategories'];
        $templateDataArray['secondaryCategoryOptions'] = $activeSecondaryCategories;
        $templateDataArray['secondaryCategoryGroups'] = $categoryOptions['secondaryCategoriesByParent'];
        $templateDataArray['pageTypeAjax'] = $this->settings['journal']['ajaxTypeNum'];
        $templateDataArray['pageNumber'] = $pageNumber;


        if (count($templateDataArray['journalRowList'])) {
            $templateDataArray['showMoreLink'] = $this->newsRepository->findByFilter(
                $categoryFilterList,
                [],
                [],
                1,
                9999,
                $filters
            )->count() > $pageNumber * $itemsPerPage ? true : false;
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

            // ajax context: Set settings manually
            $templateDataArray['settings'] = $this->settings;

            // if sysCategory and page 1 - reload whole content. Else: Append
            $kindOfRequest = $pageNumber == 1 ? 'replace' : 'append';

            $json = GeneralUtility::makeInstance(AjaxResponseBuilder::class)->build(
                [
                    [
                        'id' => 'journal-flex-container',
                        'variables' => $templateDataArray,
                        'mode' => $kindOfRequest,
                        'template' => 'Ajax/Journal/' . ucfirst($kindOfRequest),
                    ],
                    [
                        'id' => 'journal-more-link-container',
                        'variables' => $templateDataArray,
                        'mode' => 'replace',
                        'template' => 'Ajax/Journal/MoreLink',
                    ],
                ],
                $this->request,
                ['EXT:hgon_template/Resources/Private/Extension/HgonTemplate/Templates/'],
                ['EXT:hgon_template/Resources/Private/Extension/HgonTemplate/Partials/'],
                ['EXT:hgon_template/Resources/Private/Extension/HgonTemplate/Layouts/'],
                $this->settings
            );

            return $this->htmlResponse($json);

        } else {
            $this->view->assignMultiple($templateDataArray);
        }

        return $this->htmlResponse();

    }

    /**
     * @param iterable $newsList
     * @param int $journalParentCategoryUid
     * @return array<string,array>
     */
    protected function buildJournalCategoryOptions(iterable $newsList, int $journalParentCategoryUid): array
    {
        $topCategories = [];
        $secondaryCategoriesByParent = [];
        $allSecondaryCategories = [];

        foreach ($newsList as $news) {
            foreach ($news->getCategories() as $category) {
                $parentCategory = $category->getParentcategory();
                $parentUid = (int)($parentCategory?->getUid() ?? 0);
                if ($parentUid <= 0) {
                    continue;
                }

                if ($parentUid === $journalParentCategoryUid) {
                    $topCategories[$category->getUid()] = $category;
                    continue;
                }

                $secondaryCategoriesByParent[$parentUid][$category->getUid()] = $category;
                $allSecondaryCategories[$category->getUid()] = $category;
            }
        }

        $sortCategories = static function (array &$categories): void {
            uasort($categories, static function ($a, $b): int {
                $titleA = method_exists($a, 'getTitle') ? (string)$a->getTitle() : '';
                $titleB = method_exists($b, 'getTitle') ? (string)$b->getTitle() : '';
                return strcasecmp($titleA, $titleB);
            });
        };

        $sortCategories($topCategories);
        $sortCategories($allSecondaryCategories);
        foreach ($secondaryCategoriesByParent as &$categories) {
            $sortCategories($categories);
        }
        unset($categories);

        return [
            'topCategories' => $topCategories,
            'secondaryCategoriesByParent' => $secondaryCategoriesByParent,
            'allSecondaryCategories' => $allSecondaryCategories,
        ];
    }

    /**
     * @param int $primaryCategory
     * @param int $secondaryCategory
     * @param \HGON\HgonTemplate\Domain\Model\SysCategory|null $sysCategory
     * @param array<int,array> $secondaryCategoriesByParent
     * @param int $journalParentCategoryUid
     * @return array<int>
     */
    protected function resolveJournalCategoryFilterList(
        int $primaryCategory,
        int $secondaryCategory,
        ?\HGON\HgonTemplate\Domain\Model\SysCategory $sysCategory,
        array $secondaryCategoriesByParent,
        int $journalParentCategoryUid
    ): array {
        if ($secondaryCategory > 0) {
            return [$secondaryCategory];
        }

        if ($primaryCategory > 0) {
            $categoryUids = [$primaryCategory];
            if (isset($secondaryCategoriesByParent[$primaryCategory])) {
                $categoryUids = array_merge($categoryUids, array_keys($secondaryCategoriesByParent[$primaryCategory]));
            }
            return array_values(array_unique(array_map('intval', $categoryUids)));
        }

        if ($sysCategory instanceof \HGON\HgonTemplate\Domain\Model\SysCategory) {
            $selectedCategoryUid = (int)$sysCategory->getUid();
            $selectedParentUid = (int)($sysCategory->getParentcategory()?->getUid() ?? 0);

            if ($selectedParentUid === $journalParentCategoryUid) {
                $categoryUids = [$selectedCategoryUid];
                if (isset($secondaryCategoriesByParent[$selectedCategoryUid])) {
                    $categoryUids = array_merge($categoryUids, array_keys($secondaryCategoriesByParent[$selectedCategoryUid]));
                }
                return array_values(array_unique(array_map('intval', $categoryUids)));
            }

            return [$selectedCategoryUid];
        }

        return [];
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
