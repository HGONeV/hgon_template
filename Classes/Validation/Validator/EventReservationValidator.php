<?php

namespace HGON\HgonTemplate\Validation\Validator;

use \RKW\RkwBasics\Helper\Common;

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
 * Class EventReservationValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright HGON
 * @package HGON_HgonTemplate
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EventReservationValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{

    /**
     * TypoScript Settings
     *
     * @var array
     */
    protected $settings = null;

    /**
     * validation
     *
     * @var \RKW\RkwEvents\Domain\Model\EventReservation $newEventReservation
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function isValid($newEventReservation)
    {

        // initialize typoscript settings
        $this->getSettings();

        // get mandatory fields
        $mandatoryFieldsMainPerson = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(",", $this->settings['mandatoryFields']['eventReservationMainPerson']);

        $isValid = true;

        // 1. Check mandatory fields main person
        if ($mandatoryFieldsMainPerson) {

            foreach ($mandatoryFieldsMainPerson as $field) {

                $getter = 'get' . ucfirst($field);
                if (method_exists($newEventReservation, $getter)) {

                    if (
                        (
                            ($field == 'salutation')
                            && (trim($newEventReservation->$getter()) == 99)
                        )
                        ||
                        (
                            ($field != 'salutation')
                            && (!trim($newEventReservation->$getter()))
                        )
                    ) {

                        $propertyName = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'tx_rkwevents_validator.' . lcfirst($field),
                            'rkw_events'
                        );

                        $this->result->forProperty(lcfirst($field))->addError(
                            new \TYPO3\CMS\Extbase\Error\Error(
                                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                                    'tx_rkwevents_validator.not_filled',
                                    'rkw_events',
                                    array($propertyName)
                                ), 1449314603
                            )
                        );
                        $isValid = false;
                    }
                }
            }
        }



        return $isValid;
        //===
    }


    /**
     * Returns TYPO3 settings
     *
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings()
    {

        if (!$this->settings) {
            $this->settings = Common::getTyposcriptConfiguration('Rkwevents');
        }

        if (!$this->settings) {
            return array();
        }

        //===

        return $this->settings;
        //===
    }

}

