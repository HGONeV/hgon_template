<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\EventListener;

use DERHANSEN\SfEventMgt\Event\ModifyRegistrationPriceEvent;
use HGON\HgonTemplate\Service\SfEventMgtEventCulinaryService;

final class SfEventMgtRegistrationCulinaryPriceListener
{
    public function __construct(
        private readonly SfEventMgtEventCulinaryService $eventCulinaryService,
    ) {
    }

    public function __invoke(ModifyRegistrationPriceEvent $event): void
    {
        $amountOfRegistrations = $this->eventCulinaryService->getSubmittedAmountOfRegistrations(
            $event->getRequest(),
            $event->getRegistration()
        );
        $price = $event->getPrice() * $amountOfRegistrations;

        $quantities = $this->eventCulinaryService->getSubmittedQuantities($event->getRequest());
        if ($quantities !== []) {
            $selectionRows = $this->eventCulinaryService->buildSelectionRows($event->getEvent()->getUid(), $quantities);
            $price += $this->eventCulinaryService->calculateSelectionsTotal($selectionRows);
        }

        $event->setPrice(round($price, 2));
    }
}
