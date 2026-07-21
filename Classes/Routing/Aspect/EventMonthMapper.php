<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\StaticMappableAspectInterface;

final class EventMonthMapper implements StaticMappableAspectInterface, \Countable
{
    private const MONTH_PATTERN = '/^(?:19|20|21)[0-9]{2}-(?:0[1-9]|1[0-2])$/D';

    public function __construct(array $settings)
    {
    }

    public function generate(string $value): ?string
    {
        $month = substr($value, 0, 7);

        return preg_match(self::MONTH_PATTERN, $month) === 1
            && $value === $month . '-01 00:00:00'
            ? $month
            : null;
    }

    public function resolve(string $value): ?string
    {
        return preg_match(self::MONTH_PATTERN, $value) === 1
            ? $value . '-01 00:00:00'
            : null;
    }

    public function count(): int
    {
        return 3 * 100 * 12;
    }
}
