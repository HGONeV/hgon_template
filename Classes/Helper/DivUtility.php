<?php

namespace HGON\HgonTemplate\Helper;
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
 * DivUtility
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package Hgon_Template
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DivUtility extends \RKW\RkwEvents\Helper\DivUtility
{

    /**
     * check available seats
     *
     * New: Function is not changed. But if we do not overwrite it, this function calls not the overwritten function of this hgon class
     *
     *
     * @param \RKW\RkwEvents\Domain\Model\Event $event
     * @param \RKW\RkwEvents\Domain\Model\EventReservation $newEventReservation
     * @return boolean
     */
    public static function hasFreeSeats(\RKW\RkwEvents\Domain\Model\Event $event, \RKW\RkwEvents\Domain\Model\EventReservation $newEventReservation)
    {
        $countConfirmedReservations = self::countConfirmedReservations($event);
        $countEventReservationsWithAddPersons = self::countEventReservationsWithAddPersons($newEventReservation);
        $availableSeats = $event->getSeats() - $countConfirmedReservations;
        if ($availableSeats >= $countEventReservationsWithAddPersons) {
            return true;
        }

        return false;
    }


    /**
     * count reservations in eventReservation (included add Persons)
     *
     * @param \RKW\RkwEvents\Domain\Model\EventReservation $eventReservation
     * @return int
     */
    public static function countEventReservationsWithAddPersons($eventReservation)
    {
        $reservations = 1;
        $addPerson = $eventReservation->getAddPerson();
        // new: Because we have no additional persons, this count without "is_countable" throws an error
        if (is_countable($addPerson)) {
            $reservations = $reservations + count($addPerson);
        }

        return $reservations;
    }


    /**
     * workshopRegistration
     * !! returns FALSE, if not every workshop had a place for registration
     *
     * @author Maximilian Fäßler
     * @param \RKW\RkwEvents\Domain\Model\EventReservation $eventReservation
     * @return boolean
     */
    public static function workshopRegistration(\RKW\RkwEvents\Domain\Model\EventReservation $eventReservation)
    {
        // original function throws errors because workshops not included and not countable

        return false;
    }
}
