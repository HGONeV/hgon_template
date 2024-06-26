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
 * SubscriptionController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SubscriptionController extends \RKW\RkwNewsletter\Controller\SubscriptionController
{

    /**
     * action create
     *
     * @param \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser
     * @param array                                        $topics
     * @param integer                                      $terms
     * @param integer                                      $privacy
     * @validate $frontendUser \RKW\RkwNewsletter\Validation\FormValidator
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @return void
     */
    public function createAction(\RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser, $topics = array(), $terms = null, $privacy = null)
    {
        // check if terms are checked
        if (
            ($frontendUser->_isNew())
            && (!$terms)
        ) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'subscriptionController.error.acceptTerms',
                    'rkw_newsletter'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            $this->forward('new', null, null, $this->request->getArguments());
            //===
        }

        if (!$privacy) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.accept_privacy',
                    'rkw_registration'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
            $this->forward('new', null, null, $this->request->getArguments());
            //===
        }

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $subscriptions */
        $subscriptions = $this->buildCleanedTopicList($topics);

        // If no topics are selected this can't be the indention :)
        if (count($subscriptions) < 1) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'subscriptionController.error.noTopicSelected',
                    'rkw_newsletter'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
            $this->forward('new', null, null, $this->request->getArguments());
            //===
        }


        // Case 1: FE-User is not logged in and is not identified via hash-tag
        // Case 2: FE-User is not logged in, but has been identified by hash-tag
        if (
            ($frontendUser->_isNew())
            || (
                (!$frontendUser->_isNew())
                && (!$this->getFrontendUser())
            )
        ) {

            // register new user or simply send opt-in to existing user
            /** @var \RKW\RkwRegistration\Tools\Registration $registration */
            $registration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Registration');
            $registration->register(
                $frontendUser,
                false,
                [
                    'subscriptions' => $subscriptions,
                    'frontendUser'  => $frontendUser
                ],
                'rkwNewsletter',
                $this->request
            );

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'subscriptionController.message.optInCreated',
                    'rkw_newsletter'
                )
            );

            $this->forward('message');
            //===

            // Case 3: Fe-User is logged in
        } else {
            if (
                ($this->getFrontendUser())
                && ($frontendUser->getUid() == $this->getFrontendUser()->getUid())
            ) {

                // set FeUser and save
                $frontendUser->setTxRkwnewsletterSubscription($subscriptions);
                if (!$frontendUser->getTxRkwnewsletterHash()) {
                    $hash = sha1($frontendUser->getUid() . $frontendUser->getEmail() . rand());
                    $frontendUser->setTxRkwnewsletterHash($hash);
                }
                $this->frontendUserRepository->update($frontendUser);

                \RKW\RkwRegistration\Tools\Privacy::addPrivacyData($this->request, $this->getFrontendUser(), $subscriptions, 'new newsletter subscription');

                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'subscriptionController.message.subscriptionSaved',
                        'rkw_newsletter'
                    )
                );

                $this->redirect('edit');
                //===


                // Case 3: something is strange
            } else {

                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'subscriptionController.error.unexpectedError',
                        'rkw_newsletter'
                    )
                );
                $this->forward('new');
                //===
            }
        }

        $this->redirect('new');
        //===

    }



    /**
     * action message
     *
     * @return void
     */
    public function messageAction()
    {
        // nothing to do here – just look good

        $this->view->assignMultiple(
            array(
                'frontendUser' => ($this->getFrontendUser() ? $this->getFrontendUser() : $this->getFrontendUserByHash()),
            )
        );
    }

}