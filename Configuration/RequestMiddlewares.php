<?php

declare(strict_types=1);

use HGON\HgonTemplate\Middleware\SpeciesShortlinkCaseNormalization;

return [
    'frontend' => [
        'hgon-template/species-shortlink-case-normalization' => [
            'target' => SpeciesShortlinkCaseNormalization::class,
            // The resolved site is required to limit normalization to hgon_de.
            'after' => [
                'typo3/cms-frontend/base-redirect-resolver',
            ],
            // Case variants must be normalized before TYPO3 attempts its
            // case-sensitive static route and page slug resolution.
            'before' => [
                'typo3/cms-frontend/static-route-resolver',
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];
