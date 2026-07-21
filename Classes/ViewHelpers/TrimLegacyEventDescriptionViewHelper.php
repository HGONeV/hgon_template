<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class TrimLegacyEventDescriptionViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'string', 'Event description HTML', true);
    }

    public function render(): string
    {
        $value = (string)$this->arguments['value'];
        // Legacy descriptions commonly append structured metadata beginning with
        // "Ort:" or "Termin:". The list renders this information separately.
        $pattern = '~<p\b[^>]*>\s*<strong\b[^>]*>\s*(?:Ort|Termin)\s*:?\s*</strong>.*$~isu';
        $trimmedValue = preg_replace($pattern, '', $value) ?? $value;

        return preg_replace('~(?:\r?\n\s*){2,}~', "\n", trim($trimmedValue)) ?? $trimmedValue;
    }
}
