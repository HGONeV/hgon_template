<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Service;

use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageInformation;

final class PageHeaderImageResolver
{
    private const FIELD_NAME = 'tx_hgontemplate_article_image';

    /**
     * Resolves the first page header image in the current page's rootline.
     */
    public function resolve(PageInformation $pageInformation): ?FileReference
    {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);

        // TYPO3 provides the absolute rootline from the current page towards the site root.
        foreach ($pageInformation->getRootLine() as $page) {
            // FAL relations of translated pages point to the localized page UID.
            $pageUid = (int)($page['_LOCALIZED_UID'] ?? $page['uid'] ?? 0);
            if ($pageUid <= 0) {
                continue;
            }

            $files = $fileRepository->findByRelation('pages', self::FIELD_NAME, $pageUid);
            $file = reset($files);
            if ($file instanceof FileReference) {
                return $file;
            }
        }

        return null;
    }
}
