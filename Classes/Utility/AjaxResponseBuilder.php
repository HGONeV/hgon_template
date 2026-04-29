<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;

final class AjaxResponseBuilder
{
    /**
     * @param array<int, array{id: string, variables: array<string, mixed>, mode: string, template: string}> $items
     * @param string[] $templateRootPaths
     * @param string[] $partialRootPaths
     * @param string[] $layoutRootPaths
     * @param array<string, mixed> $settings
     */
    public function build(
        array $items,
        ServerRequestInterface $request,
        array $templateRootPaths,
        array $partialRootPaths,
        array $layoutRootPaths,
        array $settings = []
    ): string {
        $response = ['html' => []];

        foreach ($items as $item) {
            $variables = $item['variables'];
            $variables['settings'] ??= $settings;

            $response['html'][$item['id']] = [
                $item['mode'] => $this->renderTemplate(
                    $item['template'],
                    $variables,
                    $request,
                    $templateRootPaths,
                    $partialRootPaths,
                    $layoutRootPaths
                ),
            ];
        }

        return (string)json_encode($response, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<string, mixed> $variables
     * @param string[] $templateRootPaths
     * @param string[] $partialRootPaths
     * @param string[] $layoutRootPaths
     */
    private function renderTemplate(
        string $template,
        array $variables,
        ServerRequestInterface $request,
        array $templateRootPaths,
        array $partialRootPaths,
        array $layoutRootPaths
    ): string {
        $viewFactory = GeneralUtility::makeInstance(ViewFactoryInterface::class);
        $view = $viewFactory->create(new ViewFactoryData(
            templateRootPaths: $templateRootPaths,
            partialRootPaths: $partialRootPaths,
            layoutRootPaths: $layoutRootPaths,
            request: $request,
            format: 'html',
        ));

        $view->assignMultiple($variables);

        return $view->render($template);
    }
}
