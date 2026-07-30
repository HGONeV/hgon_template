<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Form\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;

final class CropVariantRestrictions implements FormDataProviderInterface
{
    private const RESTRICTION_MARKER = 'hgonCropVariantRestriction';

    /**
     * The first key is the CType, the second key the content element layout.
     * Add further layout-specific restrictions here.
     *
     * @var array<string, array<int, list<string>>>
     */
    private const CONTENT_ELEMENT_VARIANTS = [
        'textmedia' => [
            30 => ['16to9'],
            55 => ['1to1'],
            60 => ['2to1'],
            61 => ['16to9'],
            65 => ['default'],
            75 => ['4to3'],
            76 => ['2to1'],
            77 => ['14to5'],
            85 => ['1to1'],
        ],
    ];

    /**
     * @var array<string, array<string, list<string>>>
     */
    private const RECORD_FIELD_VARIANTS = [
        'tx_hgonspecies_domain_model_species' => [
            'image' => ['default', '4to3'],
            'dissemination_img' => ['default'],
            'sidebar_img' => ['default'],
        ],
        'tx_hgonworkgroup_domain_model_workgroup' => [
            'image' => ['default', '14to5', '3to4'],
        ],
        'tx_mdnewsauthor_domain_model_newsauthor' => [
            'image' => ['1to1'],
        ],
        'pages' => [
            'tx_hgontemplate_article_image' => ['2to1', '3to4', '4to3', '14to5', '16to9'],
        ],
        'tx_news_domain_model_news' => [
            'fal_media' => ['default', '3to4', '4to3', '16to9'],
            'tx_hgontemplate_header_image' => ['3to4', '14to5'],
        ],
        'tx_sfeventmgt_domain_model_event' => [
            'image' => ['4to3', '14to5', '3to4'],
            'additional_image' => ['default'],
        ],
    ];

    private const AVAILABLE_CROP_VARIANTS = [
        'default' => [
            'title' => 'Default',
            'selectedRatio' => 'NaN',
            'allowedAspectRatios' => [
                'NaN' => [
                    'title' => 'Frei',
                    'value' => 0.0,
                ],
            ],
        ],
        '1to1' => [
            'title' => '1:1',
            'selectedRatio' => '1:1',
            'allowedAspectRatios' => [
                '1:1' => [
                    'title' => '1:1',
                    'value' => 1.0,
                ],
            ],
        ],
        '2to1' => [
            'title' => '2:1',
            'selectedRatio' => '2:1',
            'allowedAspectRatios' => [
                '2:1' => [
                    'title' => '2:1',
                    'value' => 2.0,
                ],
            ],
        ],
        '3to4' => [
            'title' => '3:4',
            'selectedRatio' => '3:4',
            'allowedAspectRatios' => [
                '3:4' => [
                    'title' => '3:4',
                    'value' => 0.75,
                ],
            ],
        ],
        '4to3' => [
            'title' => '4:3',
            'selectedRatio' => '4:3',
            'allowedAspectRatios' => [
                '4:3' => [
                    'title' => '4:3',
                    'value' => 1.3333333333,
                ],
            ],
        ],
        '14to5' => [
            'title' => '14:5',
            'selectedRatio' => '14:5',
            'allowedAspectRatios' => [
                '14:5' => [
                    'title' => '14:5',
                    'value' => 2.8,
                ],
            ],
        ],
        '16to9' => [
            'title' => '16:9',
            'selectedRatio' => '16:9',
            'allowedAspectRatios' => [
                '16:9' => [
                    'title' => '16:9',
                    'value' => 1.7777777778,
                ],
            ],
        ],
    ];

    public function addData(array $result): array
    {
        $fieldVariants = $this->getFieldVariants($result);
        if ($fieldVariants !== []) {
            foreach ($fieldVariants as $fieldName => $variantIds) {
                $cropConfiguration = $result['processedTca']['columns'][$fieldName]['config']
                    ['overrideChildTca']['columns']['crop']['config'] ?? [];
                $result['processedTca']['columns'][$fieldName]['config']
                    ['overrideChildTca']['columns']['crop']['config'] = [
                        ...$cropConfiguration,
                        self::RESTRICTION_MARKER => true,
                        'cropVariants' => $this->getCropVariants($variantIds),
                    ];
            }

            return $result;
        }

        if (
            ($result['tableName'] ?? '') !== 'sys_file_reference'
            || empty($result['processedTca']['columns']['crop']['config'][self::RESTRICTION_MARKER])
        ) {
            return $result;
        }

        unset($result['processedTca']['columns']['crop']['config'][self::RESTRICTION_MARKER]);

        // The project-wide Page TSconfig defines all available crop variants.
        // Do not merge those variants back into this specific file reference.
        unset(
            $result['pageTsConfig']['TCEFORM.']['sys_file_reference.']['crop.']['config.']['cropVariants'],
            $result['pageTsConfig']['TCEFORM.']['sys_file_reference.']['crop.']['config.']['cropVariants.']
        );

        return $result;
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, list<string>>
     */
    private function getFieldVariants(array $result): array
    {
        $tableName = (string)($result['tableName'] ?? '');
        if ($tableName === 'tt_content') {
            $databaseRow = $result['databaseRow'] ?? [];
            $contentType = (string)$this->getSingleValue($databaseRow['CType'] ?? '');
            $layout = (int)$this->getSingleValue($databaseRow['layout'] ?? 0);
            $variantIds = self::CONTENT_ELEMENT_VARIANTS[$contentType][$layout] ?? [];

            return $variantIds === [] ? [] : ['assets' => $variantIds];
        }

        return self::RECORD_FIELD_VARIANTS[$tableName] ?? [];
    }

    /**
     * @param list<string> $variantIds
     * @return array<string, array<string, mixed>>
     */
    private function getCropVariants(array $variantIds): array
    {
        $cropVariants = [];

        foreach ($variantIds as $variantId) {
            if (isset(self::AVAILABLE_CROP_VARIANTS[$variantId])) {
                $cropVariants[$variantId] = self::AVAILABLE_CROP_VARIANTS[$variantId];
            }
        }

        return $cropVariants;
    }

    private function getSingleValue(mixed $value): mixed
    {
        return is_array($value) ? reset($value) : $value;
    }
}
