<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\EventListener;

use DERHANSEN\SfEventMgt\Event\ModifyListViewVariablesEvent;
use Doctrine\DBAL\ParameterType;
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
        $requestOnlineEvent = $this->normalizeOnlineEventFilter($pluginArguments['onlineEvent'] ?? '');
        if (!in_array($requestOnlineEvent, ['', '0', '1'], true)) {
            $requestOnlineEvent = '';
        }
        $requestWorkGroup = max(0, (int)($pluginArguments['workGroup'] ?? 0));
        $requestSearchTerm = trim((string)($pluginArguments['searchTerm'] ?? ''));
        $requestDisplayMode = (string)($pluginArguments['overwriteDemand']['displayMode'] ?? 'all');
        if (!in_array($requestDisplayMode, ['all', 'future', 'current_future', 'past', 'time_restriction'], true)) {
            $requestDisplayMode = 'all';
        }
        $requestTimeRestrictionLow = (string)($pluginArguments['overwriteDemand']['timeRestrictionLow'] ?? '');
        $requestCategory = max(0, (int)($pluginArguments['overwriteDemand']['category'] ?? 0));
        $requestTopEventRestriction = (string)($pluginArguments['overwriteDemand']['topEventRestriction'] ?? '0');
        if (!in_array($requestTopEventRestriction, ['0', '2'], true)) {
            $requestTopEventRestriction = '0';
        }
        $categoryParentUid = max(0, (int)($variables['settings']['categoryParentUid'] ?? 0));

        $variables['requestEventType'] = $requestEventType;
        $variables['requestOnlineEvent'] = $requestOnlineEvent;
        $variables['requestOnlineEventAll'] = $requestOnlineEvent === '';
        $variables['requestOnlineEventOnline'] = $requestOnlineEvent === '1';
        $variables['requestOnlineEventOnsite'] = $requestOnlineEvent === '0';
        $variables['requestWorkGroup'] = $requestWorkGroup;
        $variables['requestSearchTerm'] = $requestSearchTerm;
        $variables['requestDisplayMode'] = $requestDisplayMode;
        $variables['requestTimeRestrictionLow'] = $requestTimeRestrictionLow;
        $variables['requestCategory'] = $requestCategory;
        $variables['requestTopEventRestriction'] = $requestTopEventRestriction;
        $variables['monthOptions'] = $this->buildMonthOptions($requestTimeRestrictionLow);
        $variables['workGroupOptions'] = $this->fetchWorkGroupOptions();
        $variables['categories'] = $categoryParentUid > 0
            ? $this->fetchCategoryOptions($categoryParentUid)
            : [];
        $variables['eventTypeOptions'] = [
            'standard' => 'Standard',
            'workgroup' => 'Arbeitskreistreffen',
        ];
        $variables['onlineEventOptions'] = [
            '' => 'Alle',
            '1' => 'Online-Termine',
            '0' => 'Vor-Ort-Termine',
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

        if ($requestEventType !== '' || $requestOnlineEvent !== '' || $requestWorkGroup > 0 || $requestSearchTerm !== '') {
            $variables = $this->filterVariables(
                $variables,
                $requestEventType,
                $requestOnlineEvent,
                $requestWorkGroup,
                $requestSearchTerm,
                $pluginArguments
            );
        }

        $event->setVariables($variables);
    }

    private function normalizeOnlineEventFilter(mixed $onlineEventArgument): string
    {
        if (is_array($onlineEventArgument)) {
            $selectedValues = array_values(array_unique(array_intersect(
                array_map('strval', $onlineEventArgument),
                ['0', '1']
            )));

            return count($selectedValues) === 1 ? $selectedValues[0] : '';
        }

        return (string)$onlineEventArgument;
    }

    private function filterVariables(
        array $variables,
        string $selectedEventType,
        string $selectedOnlineEvent,
        int $selectedWorkGroup,
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

        $metadataByUid = $this->fetchEventMetadataByUid($eventArray);
        $filteredEvents = array_values(array_filter(
            $eventArray,
            fn($event): bool => $this->matchesEventFilters(
                $event,
                $metadataByUid,
                $selectedEventType,
                $selectedOnlineEvent,
                $selectedWorkGroup,
                $searchTerm
            )
        ));

        $variables['events'] = $filteredEvents;
        $variables['pagination'] = $this->buildPagination(
            $filteredEvents,
            $variables['settings']['pagination'] ?? [],
            $pluginArguments
        );

        return $variables;
    }

    private function fetchEventMetadataByUid(array $events): array
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
            ->select(
                'uid',
                'tx_hgontemplate_event_type',
                'tx_hgontemplate_online_event',
                'tx_hgon_workgroup_stdevent',
                'tx_hgon_workgroup_wgevent'
            )
            ->from('tx_sfeventmgt_domain_model_event')
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY)
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $metadataByUid = [];
        foreach ($rows as $row) {
            $metadataByUid[(int)$row['uid']] = [
                'eventType' => (string)($row['tx_hgontemplate_event_type'] ?: 'standard'),
                'onlineEvent' => (bool)$row['tx_hgontemplate_online_event'],
                'workGroups' => array_values(array_unique(array_merge(
                    $this->parseUidList((string)($row['tx_hgon_workgroup_stdevent'] ?? '')),
                    $this->parseUidList((string)($row['tx_hgon_workgroup_wgevent'] ?? ''))
                ))),
            ];
        }

        return $metadataByUid;
    }

    private function matchesEventFilters(
        object $event,
        array $metadataByUid,
        string $selectedEventType,
        string $selectedOnlineEvent,
        int $selectedWorkGroup,
        string $searchTerm
    ): bool
    {
        $metadata = $metadataByUid[$event->getUid()] ?? [
            'eventType' => 'standard',
            'onlineEvent' => false,
            'workGroups' => [],
        ];

        if ($selectedEventType !== '' && $metadata['eventType'] !== $selectedEventType) {
            return false;
        }

        if ($selectedOnlineEvent !== '' && (string)(int)$metadata['onlineEvent'] !== $selectedOnlineEvent) {
            return false;
        }

        if ($selectedWorkGroup > 0 && !in_array($selectedWorkGroup, $metadata['workGroups'], true)) {
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

    private function buildMonthOptions(string $selectedValue): array
    {
        $monthNames = [
            1 => 'Januar',
            2 => 'Februar',
            3 => 'März',
            4 => 'April',
            5 => 'Mai',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'August',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Dezember',
        ];
        $base = new \DateTimeImmutable('first day of this month 00:00:00');
        $options = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $base->modify('+' . $i . ' months');
            $value = $date->format('Y-m-d 00:00:00');
            $options[] = [
                'value' => $value,
                'label' => $monthNames[(int)$date->format('n')] . ' ' . $date->format('Y'),
                'selected' => $selectedValue === $value || ($selectedValue === '' && $i === 0),
            ];
        }

        return $options;
    }

    private function fetchWorkGroupOptions(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_hgonworkgroup_domain_model_workgroup');
        $rows = $queryBuilder
            ->select('uid', 'title')
            ->from('tx_hgonworkgroup_domain_model_workgroup')
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->orderBy('title', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(
            static fn(array $row): array => [
                'uid' => (int)$row['uid'],
                'title' => (string)$row['title'],
            ],
            $rows
        );
    }

    private function fetchCategoryOptions(int $parentCategoryUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_category');
        $rows = $queryBuilder
            ->select('uid', 'title')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter($parentCategoryUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->orderBy('sorting', 'ASC')
            ->addOrderBy('title', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(
            static fn(array $row): array => [
                'uid' => (int)$row['uid'],
                'title' => (string)$row['title'],
            ],
            $rows
        );
    }

    private function parseUidList(string $value): array
    {
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn(string $item): int => max(0, (int)$item),
            preg_split('/\s*,\s*/', $value) ?: []
        )));
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
