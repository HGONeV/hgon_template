<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\EventListener;

use DERHANSEN\SfEventMgt\Event\ModifyCreateDependingRegistrationsEvent;
use HGON\HgonTemplate\Service\SfEventMgtEventCulinaryService;

final class SfEventMgtRegistrationCulinaryDependingRegistrationsListener
{
    public function __construct(
        private readonly SfEventMgtEventCulinaryService $eventCulinaryService,
    ) {
    }

    public function __invoke(ModifyCreateDependingRegistrationsEvent $event): void
    {
        $registration = $event->getRegistration();
        if ($registration->getAmountOfRegistrations() <= 1) {
            return;
        }

        $event->setCreateDependingRegistrations(false);
        $this->eventCulinaryService->createDependingRegistrationsWithMainPriceOnly($registration);
    }
}
