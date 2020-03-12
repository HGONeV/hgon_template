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

use \RKW\RkwBasics\Helper\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
/**
 * ArticleController
 */
class ArticleController extends \GeorgRinger\News\Controller\NewsController
{
    /**
     * pagesRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\PagesRepository
     * @inject
     */
    protected $pagesRepository = null;



    /**
     * action showArticleFromPages
     *
     * @return void
     */
    public function showArticleFromPagesAction()
    {
        /** @var \HGON\HgonTemplate\Domain\Model\Pages $pages */
        $pages = $this->pagesRepository->findByIdentifier(intval($GLOBALS['TSFE']->id));
        $this->view->assign('article', $pages->getTxHgontemplateArticle());
    }



    /**
     * action newOrder
     *
     * @param \HGON\HgonTemplate\Domain\Model\Article $article
     * @return void
     */
    public function newOrderAction(\HGON\HgonTemplate\Domain\Model\Article $article)
    {
        $this->view->assign('article', $article);
    }



    /**
     * action createOrder
     * action for donation money (PayPal)
     *
     * @param \HGON\HgonTemplate\Domain\Model\Article $article
     * @return void
     */
    public function createOrderAction(\HGON\HgonTemplate\Domain\Model\Article $article)
    {
        $article->setQuantity(1);

        /** @var \HGON\HgonPayment\Domain\Model\Basket $basket */
        $basket = $this->objectManager->get('HGON\\HgonPayment\\Domain\\Model\\Basket');
        $basket->addArticle($article);

        $GLOBALS['TSFE']->fe_user->setKey('ses', 'hgon_payment_basket', $basket);
        $GLOBALS['TSFE']->storeSessionData();

        /** @var \HGON\HgonPayment\Api\PayPalApi $payPalApi */
        $payPalApi = $this->objectManager->get('HGON\\HgonPayment\\Api\\PayPalApi');

        // this var is a relict from the Donation action (where something other than paypalplus is possible)
        $isPayPalPlus = true;

        // one time payment mit PayPalPlus
        $result = $payPalApi->createPayment($basket);


        // extract approval_url
        $approvalUrl = $result->links;
        //$this->view->assign('approvalUrl', $approvalUrl[1]->href);

        // get JSON helper
        /** @var \RKW\RkwBasics\Helper\Json $jsonHelper */
        $jsonHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwBasics\\Helper\\Json');
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
        //===
    }
}
