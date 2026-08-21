<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\EventListener;

use TYPO3\CMS\Form\Mvc\Persistence\Event\AfterFormDefinitionLoadedEvent;

final class ContactFormHcaptchaListener
{
    private const PROTECTED_FORM_IDENTIFIERS = [
        'contactForm',
        'shop-Formular_1',
    ];

    public function __invoke(AfterFormDefinitionLoadedEvent $event): void
    {
        $formDefinition = $event->getFormDefinition();
        if (
            !in_array($formDefinition['identifier'] ?? '', self::PROTECTED_FORM_IDENTIFIERS, true)
            || ($formDefinition['invalid'] ?? false)
            || $this->containsHcaptcha($formDefinition['renderables'] ?? [])
        ) {
            return;
        }

        foreach ($formDefinition['renderables'] ?? [] as $pageIndex => $page) {
            if (($page['type'] ?? '') !== 'Page' || !is_array($page['renderables'] ?? null)) {
                continue;
            }

            $formDefinition['renderables'][$pageIndex]['renderables'][] = [
                'identifier' => 'hcaptcha',
                'label' => 'Sicherheitsprüfung',
                'type' => 'Hcaptcha',
                'validators' => [
                    ['identifier' => 'Hcaptcha'],
                ],
            ];
            $event->setFormDefinition($formDefinition);
            return;
        }
    }

    private function containsHcaptcha(array $renderables): bool
    {
        foreach ($renderables as $renderable) {
            if (!is_array($renderable)) {
                continue;
            }
            if (($renderable['type'] ?? '') === 'Hcaptcha') {
                return true;
            }
            if ($this->containsHcaptcha($renderable['renderables'] ?? [])) {
                return true;
            }
        }

        return false;
    }
}
