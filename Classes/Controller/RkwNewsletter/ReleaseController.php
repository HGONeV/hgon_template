<?php

namespace HGON\HgonTemplate\Controller\RkwNewsletter;
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * ReleaseController
 *
 * Hint: Some actions are only overwritten, because the HGON server has some problems with using standard params
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ReleaseController extends \RKW\RkwNewsletter\Controller\ReleaseController
{


    /**
     * action defer
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function deferAction()
    {

        $request = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_rkwnewsletter_tools_rkwnewslettermanagement');
        $issue = $this->issueRepository->findByIdentifier(intval($request['issue']));

        $issue->setStatus(98);
        $this->issueRepository->update($issue);

        $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
            'releaseController.message.issueDeferredSuccessfully',
            'rkw_newsletter'
        ), '', \TYPO3\CMS\Core\Messaging\FlashMessage::OK);


        $this->redirect('list');
        //===
    }



    /**
     * action test
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @ignorevalidation $issue
     */
    public function testAction()
    {

        $request = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_rkwnewsletter_tools_rkwnewslettermanagement');
        $issue = $this->issueRepository->findByIdentifier(intval($request['issue']));
        $emails = strval($request['emails']);
        $topic = $this->topicRepository->findByIdentifier(intval($request['topic']));
        $title = strval($request['title']);

        $emailList = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $emails);
        foreach ($emailList as $email) {
            $validateEmail = $this->validatorHelper->email($email);
            if ($validateEmail->hasErrors()) {
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'releaseController.error.emailIncorrect',
                        'rkw_newsletter',
                        [$email]
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
                );

                $this->forward("testList");
                //===
            }
        }

        /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
        $backendUser = $this->backendUserRepository->findByUid($GLOBALS['BE_USER']->user['uid']);

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\RKW\RkwNewsletter\Domain\Model\Pages> $pages */
        $pages = $this->pagesRepository->findAllByIssueAndSpecialTopic($issue);

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\RKW\RkwNewsletter\Domain\Model\Pages> $specialPages */
        $specialPages = $this->pagesRepository->findAllByIssueAndSpecialTopic($issue, true);

        // if user wants his topics only, we need only their pages!
        if ($topic) {
            $pages = $this->pagesRepository->findAllByIssueAndTopic($issue, $topic);
            // $specialPages = $this->pagesRepository->findAllByIssueAndBackendUserAndSpecialTopic($issue, $backendUser, true);
        }

        $this->getSignalSlotDispatcher()->dispatch(__CLASS__, self::SIGNAL_FOR_SENDING_MAIL_TEST, array($backendUser, $emailList, $issue, $pages, $specialPages, $title));

        $this->addFlashMessage(
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'releaseController.message.testMailSent',
                'rkw_newsletter'
            )
        );

        $this->redirect("testList");
        //===

    }



}