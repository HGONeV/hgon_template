<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\EventListener;

use DERHANSEN\SfEventMgt\Event\ModifyRegistrationValidatorResultEvent;
use HGON\HgonTemplate\Service\SfEventMgtEventCulinaryService;

final class SfEventMgtRegistrationCulinaryValidationListener
{
    public function __construct(
        private readonly SfEventMgtEventCulinaryService $eventCulinaryService,
    ) {
    }

    public function __invoke(ModifyRegistrationValidatorResultEvent $event): void
    {
        $eventUid = $this->eventCulinaryService->getSubmittedEventUid($event->getRequest(), $event->getRegistration());
        if ($eventUid <= 0) {
            return;
        }

        $quantities = $this->eventCulinaryService->getSubmittedQuantities($event->getRequest());
        if ($quantities === []) {
            return;
        }

        $amountOfRegistrations = $this->eventCulinaryService->getSubmittedAmountOfRegistrations(
            $event->getRequest(),
            $event->getRegistration()
        );
        $this->eventCulinaryService->validateSelectionRows(
            $eventUid,
            $amountOfRegistrations,
            $quantities,
            $event->getResult()
        );
    }
}
