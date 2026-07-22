<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\EventListener;

use DERHANSEN\SfEventMgt\Event\ModifyDetailViewVariablesEvent;
use HGON\HgonTemplate\Service\PageHeaderImageResolver;
use TYPO3\CMS\Frontend\Page\PageInformation;

final class SfEventMgtDetailVariablesListener
{
    public function __construct(
        private readonly PageHeaderImageResolver $pageHeaderImageResolver,
    ) {
    }

    public function __invoke(ModifyDetailViewVariablesEvent $event): void
    {
        $pageInformation = $event->getRequest()->getAttribute('frontend.page.information');
        if (!$pageInformation instanceof PageInformation) {
            return;
        }

        $variables = $event->getVariables();
        $variables['pageHeaderImage'] = $this->pageHeaderImageResolver->resolve($pageInformation);
        $event->setVariables($variables);
    }
}
