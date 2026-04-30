<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\EventListener;

use DERHANSEN\SfEventMgt\Event\ModifyListViewVariablesEvent;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
final class SfEventMgtListFilterVariablesListener
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    public function __invoke(ModifyListViewVariablesEvent $event): void
    {
        $variables = $event->getVariables();
        $request = $event->getRequest();
        $requestArguments = $request->getQueryParams()['tx_sfeventmgt_pieventlist'] ?? [];
        $pluginArguments = is_array($requestArguments) ? $requestArguments : [];

        $requestEventType = (string)($pluginArguments['eventType'] ?? '');
        if (!in_array($requestEventType, ['', 'standard', 'workgroup'], true)) {
            $requestEventType = '';
        }
        $requestSearchTerm = trim((string)($pluginArguments['searchTerm'] ?? ''));
        $requestDisplayMode = (string)($pluginArguments['overwriteDemand']['displayMode'] ?? 'all');
        if (!in_array($requestDisplayMode, ['all', 'future', 'current_future', 'past'], true)) {
            $requestDisplayMode = 'all';
        }
        $requestTopEventRestriction = (string)($pluginArguments['overwriteDemand']['topEventRestriction'] ?? '0');
        if (!in_array($requestTopEventRestriction, ['0', '2'], true)) {
            $requestTopEventRestriction = '0';
        }

        $variables['requestEventType'] = $requestEventType;
        $variables['requestSearchTerm'] = $requestSearchTerm;
        $variables['requestDisplayMode'] = $requestDisplayMode;
        $variables['requestTopEventRestriction'] = $requestTopEventRestriction;
        $variables['eventTypeOptions'] = [
            'standard' => 'Standard',
            'workgroup' => 'Arbeitskreistreffen',
        ];
        $variables['displayModeOptions'] = [
            'all' => 'Alle',
            'future' => 'Kommende',
            'current_future' => 'Laufend und kommend',
            'past' => 'Vergangene',
        ];
        $variables['topEventRestrictionOptions'] = [
            '0' => 'Alle',
            '2' => 'Nur Top-Veranstaltungen',
        ];

        if ($requestEventType !== '' || $requestSearchTerm !== '') {
            $variables = $this->filterVariables($variables, $requestEventType, $requestSearchTerm, $pluginArguments);
        }

        $event->setVariables($variables);
    }

    private function filterVariables(
        array $variables,
        string $selectedEventType,
        string $searchTerm,
        array $pluginArguments
    ): array
    {
        $events = $variables['events'] ?? [];
        if (is_array($events)) {
            $eventArray = $events;
        } elseif ($events instanceof \Traversable) {
            $eventArray = iterator_to_array($events, false);
        } else {
            $eventArray = [];
        }

        if ($eventArray === []) {
            $variables['events'] = [];
            $variables['pagination'] = null;

            return $variables;
        }

        $typesByUid = $this->fetchEventTypesByUid($eventArray);
        $filteredEvents = array_values(array_filter(
            $eventArray,
            fn($event): bool => $this->matchesEventFilters($event, $typesByUid, $selectedEventType, $searchTerm)
        ));

        $variables['events'] = $filteredEvents;
        $variables['pagination'] = $this->buildPagination(
            $filteredEvents,
            $variables['settings']['pagination'] ?? [],
            $pluginArguments
        );

        return $variables;
    }

    private function fetchEventTypesByUid(array $events): array
    {
        $uids = array_values(array_filter(array_map(
            static fn($event): int => (int)$event->getUid(),
            $events
        )));

        if ($uids === []) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_sfeventmgt_domain_model_event');
        $rows = $queryBuilder
            ->select('uid', 'tx_hgontemplate_event_type')
            ->from('tx_sfeventmgt_domain_model_event')
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY)
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $typesByUid = [];
        foreach ($rows as $row) {
            $typesByUid[(int)$row['uid']] = (string)($row['tx_hgontemplate_event_type'] ?: 'standard');
        }

        return $typesByUid;
    }

    private function matchesEventFilters(object $event, array $typesByUid, string $selectedEventType, string $searchTerm): bool
    {
        if ($selectedEventType !== '' && ($typesByUid[$event->getUid()] ?? 'standard') !== $selectedEventType) {
            return false;
        }

        if ($searchTerm === '') {
            return true;
        }

        $needle = mb_strtolower($searchTerm);
        $haystacks = [
            (string)($event->getTitle() ?? ''),
            (string)($event->getTeaser() ?? ''),
            (string)($event->getDescription() ?? ''),
        ];

        $location = method_exists($event, 'getLocation') ? $event->getLocation() : null;
        if (is_object($location) && method_exists($location, 'getTitle')) {
            $haystacks[] = (string)($location->getTitle() ?? '');
        }

        if (method_exists($event, 'getCategories')) {
            foreach ($event->getCategories() ?? [] as $category) {
                if (is_object($category) && method_exists($category, 'getTitle')) {
                    $haystacks[] = (string)($category->getTitle() ?? '');
                }
            }
        }

        foreach ($haystacks as $haystack) {
            $plainHaystack = trim(strip_tags($haystack));
            if ($plainHaystack !== '' && mb_stripos($plainHaystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function buildPagination(array $events, array $settings, array $pluginArguments): ?array
    {
        if (!(bool)($settings['enablePagination'] ?? false) || (int)($settings['itemsPerPage'] ?? 0) <= 0) {
            return null;
        }

        $currentPage = isset($pluginArguments['currentPage']) ? max(1, (int)$pluginArguments['currentPage']) : 1;
        $itemsPerPage = (int)($settings['itemsPerPage'] ?? 10);
        $maxNumPages = (int)($settings['maxNumPages'] ?? 10);

        $paginator = new ArrayPaginator($events, $currentPage, $itemsPerPage);
        $pagination = new SlidingWindowPagination($paginator, $maxNumPages);

        return [
            'paginator' => $paginator,
            'pagination' => $pagination,
        ];
    }
}
