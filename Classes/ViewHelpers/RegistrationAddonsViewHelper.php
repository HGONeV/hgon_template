<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\ViewHelpers;

use DERHANSEN\SfEventMgt\Domain\Model\Registration;
use HGON\HgonTemplate\Service\SfEventMgtEventCulinaryService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class RegistrationAddonsViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('registration', Registration::class, 'Registration', true);
    }

    public function render(): array
    {
        /** @var Registration $registration */
        $registration = $this->arguments['registration'];
        if ($registration->getUid() <= 0) {
            return [];
        }

        /** @var SfEventMgtEventCulinaryService $service */
        $service = GeneralUtility::makeInstance(SfEventMgtEventCulinaryService::class);

        return $service->getRegistrationAddons((int)$registration->getUid());
    }
}
