<?php
namespace HGON\HgonTemplate\Controller;

use RKW\RkwEvents\Helper\DivUtility;
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
     * action create
     *
     * @param \RKW\RkwEvents\Domain\Model\EventReservation $newEventReservation
     * @param integer $terms
     * @param integer $privacy
     * @validate $newEventReservation \RKW\RkwEvents\Validation\Validator\EventReservationValidator
     * @return void
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function createAction(\RKW\RkwEvents\Domain\Model\EventReservation $newEventReservation, $terms = null, $privacy = null)
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

                // @toDo: Nachricht muss wahrscheinlich angepasst werden (paramter wurden entfernt)
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'eventReservationController.hint.reservations',
                        'rkw_events'
                    )
                );

                // already registered
                $this->redirect('end', 'EventReservation', null, array('event' => $newEventReservation->getEvent()), intval($this->settings['showPid']));
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
            $this->redirect('end', 'EventReservation', null, array('event' => $newEventReservation->getEvent()), $this->settings['showPid']);
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

            $this->redirect('end', 'EventReservation', null, array('event' => $newEventReservation->getEvent()), intval($this->settings['showPid']));
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
                'eventReservationController.message.reservationCreatedEmail', 'rkw_events',
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::OK
            )
        );

        $this->forward('end', null, null, array('event' => $newEventReservation->getEvent()));
        //===
    }



    /**
     * action end
     *
     */
    public function endAction()
    {
        // just show messages
    }
}