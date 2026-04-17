<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\EventListener;

use DERHANSEN\SfEventMgt\Event\ModifyRegistrationViewVariablesEvent;
use HGON\HgonTemplate\Service\SfEventMgtEventCulinaryService;

final class SfEventMgtRegistrationCulinaryVariablesListener
{
    public function __construct(
        private readonly SfEventMgtEventCulinaryService $eventCulinaryService,
    ) {
    }

    public function __invoke(ModifyRegistrationViewVariablesEvent $event): void
    {
        $variables = $event->getVariables();
        $registration = $variables['registration'] ?? null;
        $eventModel = $variables['event'] ?? null;
        $eventUid = is_object($eventModel) && method_exists($eventModel, 'getUid') ? (int)$eventModel->getUid() : 0;
        $quantities = $this->eventCulinaryService->getSubmittedQuantities($event->getRequest());
        $amountOfRegistrations = $this->eventCulinaryService->getSubmittedAmountOfRegistrations(
            $event->getRequest(),
            $registration
        );

        $variables['eventCulinarySelections'] = $this->eventCulinaryService->buildSelectionRows($eventUid, $quantities);
        $variables['eventCulinaryMaxQuantity'] = $amountOfRegistrations;

        $event->setVariables($variables);
    }
}
