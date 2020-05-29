<?php
namespace HGON\HgonTemplate\Controller;

use RKW\RkwEvents\Helper\DivUtility;
//use HGON\HgonTemplate\Helper\DivUtility;
use \RKW\RkwBasics\Helper\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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

/**
 * Class EventReservationController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package Hgon_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EventReservationController extends \RKW\RkwEvents\Controller\EventReservationController
{
    /**
     * eventRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\EventRepository
     * @inject
     */
    protected $eventRepository = null;

    /**
     * eventCulinaryRepository
     *
     * @var \HGON\HgonTemplate\Domain\Repository\EventCulinaryRepository
     * @inject
     */
    protected $eventCulinaryRepository = null;

    /**
     * action new
     *
     * @param \RKW\RkwEvents\Domain\Model\Event $event
     * @param \RKW\RkwEvents\Domain\Model\EventReservation $newEventReservation
     * @ignorevalidation $event
     * @ignorevalidation $newEventReservation
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function newAction(\RKW\RkwEvents\Domain\Model\Event $event = null, \RKW\RkwEvents\Domain\Model\EventReservation $newEventReservation = null)
    {
        if ($event instanceof \RKW\RkwEvents\Domain\Model\Event) {
            // Workaround: We need a HGON-Event. But we can't change phpDocs (because its an extended action)
            $event = $this->eventRepository->findByIdentifier($event->getUid());
        }

        // if we're in new plugin context (for reservation)
        if (!$event) {
            $getParams = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_rkwevents_rkweventsreservation');
            $eventUid = preg_replace('/[^0-9]/', '', $getParams['event']);
            $event = $this->eventRepository->findByUid($eventUid);
        }
        // Difference to original action: Because we open the reservation via lightbox within the
        // standard event show, we have to grab the standard event data
        if (!$event) {
            $getParams = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_rkwevents_pi1');
            $eventUid = preg_replace('/[^0-9]/', '', $getParams['event']);
            $event = $this->eventRepository->findByUid($eventUid);
        }

        // add new typeNum for reservation ajax requests
        $this->view->assign('ajaxTypeNumReservation', $this->settings['ajaxTypeNumReservation']);

        parent::newAction($event, $newEventReservation);
    }


    /**
     * initialize create action
     *
     * @return void
     */
    public function initializeCreateAlternativeAction()
    {
        // needed, because not selected culinary checkboxes are empty. And this throws an error
        if ($this->arguments->hasArgument('newEventReservation')) {

            $request = $this->request->getArguments();
            // always: Filter empty elements
            if (key_exists('txHgontemplateEventculinary', $request['newEventReservation'])) {
                $request['newEventReservation']['txHgontemplateEventculinary'] = array_filter($request['newEventReservation']['txHgontemplateEventculinary']);

                // do only ignore, if no content is set. Otherwise we got no objects
                if (!array_sum($request['newEventReservation']['txHgontemplateEventculinary'])) {
                    $this->arguments->getArgument('newEventReservation')->getPropertyMappingConfiguration()->skipProperties('txHgontemplateEventculinary');
                }
            }

            // reset filtered content with no empty array entries
            $this->request->setArgument('newEventReservation', $request['newEventReservation']);
        }
    }


    /**
     * action createAlternative
     *
     * @param \HGON\HgonTemplate\Domain\Model\EventReservation $newEventReservation
     * @param integer $terms
     * @param integer $privacy
     * @validate $newEventReservation \HGON\HgonTemplate\Validation\Validator\EventReservationValidator
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function createAlternativeAction(\HGON\HgonTemplate\Domain\Model\EventReservation $newEventReservation, $terms = null, $privacy = null)
    {
        // 1. Check for existing reservations based on email.
        $frontendUser = $this->frontendUserRepository->findByUsername($newEventReservation->getEmail());
        if (count($frontendUser)) {

            $eventReservationResult = $this->eventReservationRepository->findByEventAndFeUser($newEventReservation->getEvent(), $frontendUser);
            if (count($eventReservationResult)) {

                // already registered!
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'eventReservationController.error.exists', 'rkw_events'
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
                );

                // already registered
                $this->forward('end', null, null, array('event' => $newEventReservation->getEvent()));
                //===
            }
        }

        // 2. Check available seats
        if (!DivUtility::hasFreeSeats($newEventReservation->getEvent(), $newEventReservation)) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'eventReservationController.error.seats', 'rkw_events'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
            $this->forward('end', null, null, array('event' => $newEventReservation->getEvent()));
            //===
        }

        // 3. Check if registration-time is over since the user may have been waiting too long
        if (DivUtility::hasRegTimeEnded($newEventReservation->getEvent())) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'eventReservationController.error.registration_time', 'rkw_events'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            $this->forward('end', null, null, array('event' => $newEventReservation->getEvent()));
            //===
        }


        // 4. Check for terms
        if (!$terms) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'eventReservationController.error.acceptTerms', 'rkw_events'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            $this->forward('new', null, null, array('newEventReservation' => $newEventReservation, 'event' => $newEventReservation->getEvent()));
            //===
        }

        // 5. check if email is valid
        if (!\RKW\RkwRegistration\Tools\Registration::validEmail($newEventReservation->getEmail())) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'eventReservationController.error.no_valid_email', 'rkw_events'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            $this->forward('new', null, null, array('newEventReservation' => $newEventReservation, 'event' => $newEventReservation->getEvent()));
            //===
        }


        // 6. privacy
        if (!$privacy) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'registrationController.error.accept_privacy', 'rkw_registration'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
            $this->forward('new', null, null, array('newEventReservation' => $newEventReservation, 'event' => $newEventReservation->getEvent()));
            //===
        }


        // HGON EDIT START: Culinary and PayPal-Handling
        if (
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('hgon_payment')
            && $newEventReservation->getTxHgontemplatePaymenttype() == 2
        ) {

            if (
                $newEventReservation->getTxHgontemplateEventculinary()->count()
                || ($newEventReservation->getEvent()->getCostsReg() || $newEventReservation->getEvent()->getCostsRed())
            )  {
                /** @var \HGON\HgonPayment\Domain\Model\Basket $basket */
                $basket = $this->objectManager->get('HGON\\HgonPayment\\Domain\\Model\\Basket');

                // add culinary
                if ($newEventReservation->getTxHgontemplateEventculinary()->count()) {
                    /** @var \HGON\HgonTemplate\Domain\Model\EventCulinary $eventCulinary */
                    foreach ($newEventReservation->getTxHgontemplateEventculinary() as $eventCulinary) {
                        /** @var \HGON\HgonPayment\Domain\Model\Article $article */
                        $article = $this->objectManager->get('HGON\\HgonPayment\\Domain\\Model\\Article');
                        $article->setDescription($eventCulinary->getDescription());
                        $article->setName($eventCulinary->getTitle());
                        $article->setPrice(floatval($eventCulinary->getPrice()));
                        $article->setSku('culinary' . $eventCulinary->getUid());
                        $article->setIsDonation(false);
                        $basket->addArticle($article);
                    }
                }

                // add possible event costs
                if (
                    $newEventReservation->getEvent()->getCostsReg()
                    || $newEventReservation->getEvent()->getCostsRed()
                ) {
                    $article = $this->objectManager->get('HGON\\HgonPayment\\Domain\\Model\\Article');
                   // $article->setDescription('');
                    $article->setName('Kosten Teilnahme');
                    // regular price
                    if ($newEventReservation->getTxHgontemplateEventcosts() == 'reg') {
                        $article->setPrice(floatval($newEventReservation->getEvent()->getCostsReg()));
                    } elseif ($newEventReservation->getTxHgontemplateEventcosts() == 'red') {
                        // reduced price
                        $article->setPrice(floatval($newEventReservation->getEvent()->getCostsRed()));
                    }
                    $article->setSku('eventprice' . $newEventReservation->getEvent()->getUid());
                    $article->setIsDonation(false);
                    $basket->addArticle($article);
                }

                $GLOBALS['TSFE']->fe_user->setKey('ses', 'hgon_payment_basket', $basket);
                $GLOBALS['TSFE']->storeSessionData();

                /** @var \HGON\HgonPayment\Api\PayPalApi $payPalApi */
                $payPalApi = $this->objectManager->get('HGON\\HgonPayment\\Api\\PayPalApi');
                $result = $payPalApi->createPayment($basket);
                // extract approval_url
                $approvalUrlArray = $result->links;
                $approvalUrl = $approvalUrlArray[1]->href;
            }
        }
        // HGON EDIT END

        // register new user or simply send opt-in to user
        /** @var \RKW\RkwRegistration\Tools\Registration $registration */
        $registration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Registration');
        $registration->register(
            array(
                'tx_rkwregistration_gender' => $newEventReservation->getSalutation(),
                'first_name'                => $newEventReservation->getFirstName(),
                'last_name'                 => $newEventReservation->getLastName(),
                'company'                   => $newEventReservation->getCompany(),
                'address'                   => $newEventReservation->getAddress(),
                'zip'                       => $newEventReservation->getZip(),
                'city'                      => $newEventReservation->getCity(),
                'email'                     => $newEventReservation->getEmail(),
                'username'                  => ($this->getFrontendUser() ? $this->getFrontendUser()->getUsername() : $newEventReservation->getEmail()),
            ),
            false,
            $newEventReservation,
            'rkwEvents',
            $this->request
        );

        $this->addFlashMessage(
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'eventReservationController.message.reservationCreatedEmailAlt', 'hgon_template',
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::OK
            )
        );

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('hgon_payment')) {
            $this->forward('end', null, null, array('event' => $newEventReservation->getEvent(), 'approvalUrl' => $approvalUrl));
            //===
        } else {
            $this->forward('end', null, null, array('event' => $newEventReservation->getEvent()));
            //===
        }
    }



    /**
     * action end
     *
     * @param \RKW\RkwEvents\Domain\Model\Event $event
     * @param string $approvalUrl
     * @return void
     */
    public function endAction($event, $approvalUrl = '')
    {
        if ($approvalUrl) {
            $this->view->assign('approvalUrl', $approvalUrl);
        }

        // just show messages
    }

}