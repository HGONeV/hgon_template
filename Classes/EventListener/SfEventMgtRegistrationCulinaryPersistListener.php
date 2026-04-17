<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\EventListener;

use DERHANSEN\SfEventMgt\Event\AfterRegistrationSavedEvent;
use HGON\HgonTemplate\Service\SfEventMgtEventCulinaryService;

final class SfEventMgtRegistrationCulinaryPersistListener
{
    public function __construct(
        private readonly SfEventMgtEventCulinaryService $eventCulinaryService,
    ) {
    }

    public function __invoke(AfterRegistrationSavedEvent $event): void
    {
        $registration = $event->getRegistration();
        $eventUid = $this->eventCulinaryService->getSubmittedEventUid($event->getRequest(), $registration);
        if ($eventUid <= 0) {
            return;
        }

        $quantities = $this->eventCulinaryService->getSubmittedQuantities($event->getRequest());
        if ($quantities === []) {
            return;
        }

        $selectionRows = $this->eventCulinaryService->buildSelectionRows($eventUid, $quantities);
        $this->eventCulinaryService->persistRegistrationAddons($registration, $selectionRows);
    }
}
