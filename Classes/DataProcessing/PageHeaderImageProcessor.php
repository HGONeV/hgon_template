<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\DataProcessing;

use HGON\HgonTemplate\Service\PageHeaderImageResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\Page\PageInformation;

/**
 * Resolves the first page header image in the current page's rootline.
 */
final class PageHeaderImageProcessor implements DataProcessorInterface
{
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $targetVariable = $cObj->stdWrapValue('as', $processorConfiguration, 'pageArticleImage');
        $processedData[$targetVariable] = null;

        $pageInformation = $cObj->getRequest()->getAttribute('frontend.page.information');
        if (!$pageInformation instanceof PageInformation) {
            return $processedData;
        }

        $processedData[$targetVariable] = GeneralUtility::makeInstance(PageHeaderImageResolver::class)
            ->resolve($pageInformation);

        return $processedData;
    }
}
