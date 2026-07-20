<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\EventListener;

use DERHANSEN\SfEventMgt\Event\ModifyRegistrationViewVariablesEvent;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;

final class SfEventMgtRegistrationFormVariablesListener
{
    private const EDITORIAL_EVENT_FORM = '1:/form_definitions/veranstaltungsanmeldungRedaktionell.form.yaml';

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    public function __invoke(ModifyRegistrationViewVariablesEvent $event): void
    {
        $variables = $event->getVariables();
        $eventModel = $variables['event'] ?? null;
        $eventUid = is_object($eventModel) && method_exists($eventModel, 'getUid') ? (int)$eventModel->getUid() : 0;

        $variables['registrationMode'] = 'native';
        $variables['registrationFormPersistenceIdentifier'] = '';
        $variables['registrationFormOverrideConfiguration'] = [];
        $variables['registrationEventIsOnline'] = false;

        if ($eventUid > 0) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_sfeventmgt_domain_model_event');
            $row = $queryBuilder
                ->select(
                    'tx_hgontemplate_registration_mode',
                    'tx_hgontemplate_registration_form',
                    'tx_hgontemplate_online_event'
                )
                ->from('tx_sfeventmgt_domain_model_event')
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter($eventUid, ParameterType::INTEGER)
                    )
                )
                ->executeQuery()
                ->fetchAssociative();

            if (is_array($row)) {
                $variables['registrationMode'] = (string)($row['tx_hgontemplate_registration_mode'] ?: 'native');
                $variables['registrationFormPersistenceIdentifier'] = (string)($row['tx_hgontemplate_registration_form'] ?? '');
                $variables['registrationEventIsOnline'] = (bool)$row['tx_hgontemplate_online_event'];
                if (
                    $variables['registrationMode'] === 'form'
                    && $variables['registrationFormPersistenceIdentifier'] === self::EDITORIAL_EVENT_FORM
                    && is_object($eventModel)
                ) {
                    $variables['registrationFormOverrideConfiguration'] = $this->buildEditorialEventFormOverrideConfiguration(
                        $eventModel,
                        $variables['registrationEventIsOnline'],
                        $eventUid
                    );
                }
            }
        }

        $event->setVariables($variables);
    }

    private function buildEditorialEventFormOverrideConfiguration(object $eventModel, bool $isOnlineEvent, int $eventUid): array
    {
        $eventTitle = method_exists($eventModel, 'getTitle') ? trim((string)$eventModel->getTitle()) : '';
        $eventDate = $this->formatEventDate($eventModel);
        $eventLocation = $isOnlineEvent ? '' : $this->formatEventLocation($eventModel);

        return [
            'renderingOptions' => [
                'additionalParams' => [
                    'tx_sfeventmgt_pieventregistration' => [
                        'event' => $eventUid,
                    ],
                ],
            ],
            'renderables' => [
                0 => [
                    'renderables' => [
                        0 => [
                            'defaultValue' => $eventTitle,
                        ],
                        1 => [
                            'defaultValue' => $eventDate,
                        ],
                        2 => [
                            'defaultValue' => $eventLocation,
                        ],
                    ],
                ],
            ],
            'finishers' => [
                0 => [
                    'options' => [
                        'variables' => [
                            'eventRegistrationMailRecipient' => 'receiver',
                            'eventRegistrationEventTitle' => $eventTitle,
                            'eventRegistrationEventDate' => $eventDate,
                        ],
                    ],
                ],
                1 => [
                    'options' => [
                        'variables' => [
                            'eventRegistrationMailRecipient' => 'sender',
                            'eventRegistrationEventTitle' => $eventTitle,
                            'eventRegistrationEventDate' => $eventDate,
                        ],
                    ],
                ],
            ],
        ];
    }

    private function formatEventDate(object $eventModel): string
    {
        $startDate = method_exists($eventModel, 'getStartdate') ? $eventModel->getStartdate() : null;
        $endDate = method_exists($eventModel, 'getEnddate') ? $eventModel->getEnddate() : null;

        if (!$startDate instanceof \DateTimeInterface) {
            return '';
        }

        $formatted = $startDate->format('d.m.Y, H:i');
        if ($endDate instanceof \DateTimeInterface) {
            $formatted .= ' - ' . $endDate->format('H:i');
        }

        return $formatted . ' Uhr';
    }

    private function formatEventLocation(object $eventModel): string
    {
        $location = method_exists($eventModel, 'getLocation') ? $eventModel->getLocation() : null;
        if (!is_object($location)) {
            return '';
        }

        $parts = [];
        foreach (['getTitle', 'getAddress'] as $methodName) {
            if (method_exists($location, $methodName)) {
                $value = trim((string)$location->{$methodName}());
                if ($value !== '') {
                    $parts[] = $value;
                }
            }
        }

        $zip = method_exists($location, 'getZip') ? trim((string)$location->getZip()) : '';
        $city = method_exists($location, 'getCity') ? trim((string)$location->getCity()) : '';
        $zipCity = trim($zip . ' ' . $city);
        if ($zipCity !== '') {
            $parts[] = $zipCity;
        }

        return implode(', ', $parts);
    }
}
