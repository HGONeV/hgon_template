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

use HGON\HgonPayment\Session\BasketSessionService;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use \HGON\HgonTemplate\Utility\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
/**
 * ArticleController
 */
class ArticleController extends \GeorgRinger\News\Controller\NewsController
{
    protected BasketSessionService $basketSessionService;

    /**
     * @var \HGON\HgonTemplate\Domain\Repository\PagesRepository
     */
    protected \HGON\HgonTemplate\Domain\Repository\PagesRepository $pagesRepository;

    public function __construct(
        ?BasketSessionService $basketSessionService = null,
        ?\HGON\HgonTemplate\Domain\Repository\PagesRepository $pagesRepository = null
    ) {
        $this->basketSessionService = $basketSessionService ?? GeneralUtility::makeInstance(BasketSessionService::class);
        $this->pagesRepository = $pagesRepository ?? GeneralUtility::makeInstance(\HGON\HgonTemplate\Domain\Repository\PagesRepository::class);
    }

    public function injectPagesRepository(\HGON\HgonTemplate\Domain\Repository\PagesRepository $pagesRepository): void
    {
        $this->pagesRepository = $pagesRepository;
    }

    public function addToBasketAction(): \Psr\Http\Message\ResponseInterface
    {
        $basket = $this->basketSessionService->getBasket();
        // ... basket anpassen ...
        // $this->basketSessionService->setBasket($basket);

        return $this->htmlResponse();
    }

    public function clearBasketAction(): \Psr\Http\Message\ResponseInterface
    {
        $this->basketSessionService->clearBasket();

        return $this->htmlResponse();
    }



    /**
     * action showArticleFromPages
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function showArticleFromPagesAction()
    {
        /** @var \HGON\HgonTemplate\Domain\Model\Pages $pages */
        $pages = $this->pagesRepository->findByIdentifier(
            (int)($this->request->getAttribute('frontend.page.information')?->getId() ?? 0)
        );
        $this->view->assign('article', $pages->getTxHgontemplateArticle());

        return $this->htmlResponse();
    }



    /**
     * action newOrder
     *
     * @param \HGON\HgonTemplate\Domain\Model\Article $article
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function newOrderAction(\HGON\HgonTemplate\Domain\Model\Article $article)
    {
        $this->view->assign('article', $article);

        return $this->htmlResponse();
    }



    /**
     * action createOrder
     * action for donation money (PayPal)
     *
     * @param \HGON\HgonTemplate\Domain\Model\Article $article
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createOrderAction(\HGON\HgonTemplate\Domain\Model\Article $article)
    {
        $article->setQuantity(1);

        /** @var \HGON\HgonPayment\Domain\Model\Basket $basket */
        $basket = GeneralUtility::makeInstance(\HGON\HgonPayment\Domain\Model\Basket::class);
        $basket->addArticle($article);

        $this->basketSessionService->setBasket($basket);

        /** @var \HGON\HgonPayment\Api\PayPalApi $payPalApi */
        $payPalApi = GeneralUtility::makeInstance(\HGON\HgonPayment\Api\PayPalApi::class);

        // this var is a relict from the Donation action (where something other than paypalplus is possible)
        $isPayPalPlus = true;

        // one time payment mit PayPalPlus
        $result = $payPalApi->createPayment($basket);


        // extract approval_url
        $approvalUrl = $result->links;
        //$this->view->assign('approvalUrl', $approvalUrl[1]->href);

        // get JSON helper
        /** @var \RKW\RkwBasics\Helper\Json $jsonHelper */
        $jsonHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\RKW\RkwBasics\Helper\Json::class);
        // get new list
        $replacements = array (
            'approvalUrl' => $isPayPalPlus ? $approvalUrl[1]->href : $approvalUrl[0]->href,
            'isPayPalPlus' => $isPayPalPlus
        );

        $jsonHelper->setHtml(
            'payment-container',
            $replacements,
            'replace',
            'Ajax/Article/CreateOrder.html'
        );

        print (string) $jsonHelper;
        exit();

        return $this->htmlResponse();
    }
}
