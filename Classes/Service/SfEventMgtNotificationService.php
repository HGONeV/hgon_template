<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Service;

use DERHANSEN\SfEventMgt\Domain\Model\Event;
use DERHANSEN\SfEventMgt\Domain\Model\Registration;
use DERHANSEN\SfEventMgt\Service\NotificationService;
use DERHANSEN\SfEventMgt\Utility\MessageType;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

final class SfEventMgtNotificationService extends NotificationService
{
    public function sendAdminMessage(
        RequestInterface $request,
        Event $event,
        Registration $registration,
        array $settings,
        int $type
    ): bool {
        if ($this->isUnconfirmedRegistrationAdminMessageDisabled($settings, $type)) {
            return false;
        }

        return parent::sendAdminMessage($request, $event, $registration, $settings, $type);
    }

    private function isUnconfirmedRegistrationAdminMessageDisabled(array $settings, int $type): bool
    {
        if ($type === MessageType::REGISTRATION_NEW) {
            return (bool)($settings['notification']['registrationNew']['adminDisabled'] ?? false);
        }

        if ($type === MessageType::REGISTRATION_WAITLIST_NEW) {
            return (bool)($settings['notification']['registrationWaitlistNew']['adminDisabled'] ?? false);
        }

        return false;
    }
}
