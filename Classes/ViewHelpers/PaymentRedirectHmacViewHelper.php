<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\ViewHelpers;

use DERHANSEN\SfEventMgt\Domain\Model\Registration;
use DERHANSEN\SfEventMgt\Security\HashScope;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Creates the signed HMAC required to restart an event payment.
 */
final class PaymentRedirectHmacViewHelper extends AbstractViewHelper
{
    public function __construct(
        private readonly HashService $hashService,
    ) {
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('registration', Registration::class, 'Event registration', true);
    }

    public function render(): string
    {
        /** @var Registration $registration */
        $registration = $this->arguments['registration'];

        return $this->hashService->hmac(
            'redirectAction-' . $registration->getUid(),
            HashScope::PaymentAction->value
        );
    }
}
