<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\EventListener;

use DERHANSEN\SfEventMgt\Event\ModifyRegistrationViewVariablesEvent;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;

final class SfEventMgtRegistrationFormVariablesListener
{
    private const EDITORIAL_EVENT_FORM = 'EXT:hgon_template/Configuration/Yaml/FormFramework/Forms/veranstaltungsanmeldungRedaktionell.form.yaml';

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

        if ($eventUid > 0) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_sfeventmgt_domain_model_event');
            $row = $queryBuilder
                ->select('tx_hgontemplate_registration_mode', 'tx_hgontemplate_registration_form')
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
                if (
                    $variables['registrationMode'] === 'form'
                    && $variables['registrationFormPersistenceIdentifier'] === self::EDITORIAL_EVENT_FORM
                    && is_object($eventModel)
                ) {
                    $variables['registrationFormOverrideConfiguration'] = $this->buildEditorialEventFormOverrideConfiguration($eventModel);
                }
            }
        }

        $event->setVariables($variables);
    }

    private function buildEditorialEventFormOverrideConfiguration(object $eventModel): array
    {
        $eventTitle = method_exists($eventModel, 'getTitle') ? trim((string)$eventModel->getTitle()) : '';
        $eventDate = $this->formatEventDate($eventModel);
        $eventLocation = $this->formatEventLocation($eventModel);

        return [
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
