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
    private const WORKGROUP_CATEGORY_UID = 105;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    public function __invoke(ModifyListViewVariablesEvent $event): void
    {
        $variables = $event->getVariables();
        $request = $event->getRequest();
        $queryParameters = $request->getQueryParams();
        $requestArguments = $queryParameters['tx_sfeventmgt_pieventlist'] ?? [];
        $pluginArguments = is_array($requestArguments) ? $requestArguments : [];

        $requestOnlineEvent = $this->normalizeOnlineEventFilter(
            $this->getScalarQueryParameter($queryParameters, 'online')
                ?? ($pluginArguments['onlineEvent'] ?? '')
        );
        if (!in_array($requestOnlineEvent, ['', '0', '1'], true)) {
            $requestOnlineEvent = '';
        }
        $requestWorkGroup = max(0, (int)(
            $this->getScalarQueryParameter($queryParameters, 'kreis')
                ?? ($pluginArguments['workGroup'] ?? 0)
        ));
        $requestSearchTerm = trim(
            $this->getScalarQueryParameter($queryParameters, 'suche')
                ?? (string)($pluginArguments['searchTerm'] ?? '')
        );
        $requestDisplayMode = (string)($pluginArguments['overwriteDemand']['displayMode'] ?? 'all');
        if (!in_array($requestDisplayMode, ['all', 'future', 'current_future', 'past', 'time_restriction'], true)) {
            $requestDisplayMode = 'all';
        }
        $requestTimeRestrictionLow = (string)($pluginArguments['overwriteDemand']['timeRestrictionLow'] ?? '');
        $requestCategory = max(0, (int)(
            $this->getScalarQueryParameter($queryParameters, 'kategorie')
                ?? ($pluginArguments['overwriteDemand']['category'] ?? 0)
        ));
        $legacyEventType = $this->getScalarQueryParameter($queryParameters, 'typ')
            ?? (is_scalar($pluginArguments['eventType'] ?? null) ? (string)$pluginArguments['eventType'] : '');
        if ($requestCategory === 0 && in_array($legacyEventType, ['arbeitskreis', 'workgroup'], true)) {
            $requestCategory = self::WORKGROUP_CATEGORY_UID;
        }
        $requestTopEventRestriction = $this->getScalarQueryParameter($queryParameters, 'top') === '1'
            ? '2'
            : (string)($pluginArguments['overwriteDemand']['topEventRestriction'] ?? '0');
        if (!in_array($requestTopEventRestriction, ['0', '2'], true)) {
            $requestTopEventRestriction = '0';
        }
        $categoryParentUid = max(0, (int)($variables['settings']['categoryParentUid'] ?? 0));

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
        $variables['filterQueryParameters'] = $this->buildFilterQueryParameters(
            $requestOnlineEvent,
            $requestWorkGroup,
            $requestSearchTerm,
            $requestCategory,
            $requestTopEventRestriction
        );
        $variables['monthOptions'] = $this->buildMonthOptions($requestTimeRestrictionLow);
        $variables['workGroupOptions'] = $this->fetchWorkGroupOptions();
        $variables['categories'] = $categoryParentUid > 0
            ? $this->fetchCategoryOptions($categoryParentUid)
            : [];
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

        if (
            $requestOnlineEvent !== ''
            || $requestWorkGroup > 0
            || $requestSearchTerm !== ''
            || $requestCategory > 0
            || $requestTopEventRestriction === '2'
        ) {
            $variables = $this->filterVariables(
                $variables,
                $requestOnlineEvent,
                $requestWorkGroup,
                $requestSearchTerm,
                $requestCategory,
                $requestTopEventRestriction,
                $pluginArguments
            );
        }

        $event->setVariables($variables);
    }

    private function getScalarQueryParameter(array $queryParameters, string $name): ?string
    {
        $value = $queryParameters[$name] ?? null;

        return is_scalar($value) ? (string)$value : null;
    }

    private function buildFilterQueryParameters(
        string $onlineEvent,
        int $workGroup,
        string $searchTerm,
        int $category,
        string $topEventRestriction
    ): array {
        return array_filter([
            'kategorie' => $category > 0 ? $category : null,
            'kreis' => $workGroup > 0 ? $workGroup : null,
            'suche' => $searchTerm !== '' ? $searchTerm : null,
            'top' => $topEventRestriction === '2' ? 1 : null,
            'online' => $onlineEvent !== '' ? $onlineEvent : null,
        ], static fn(mixed $value): bool => $value !== null);
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
        string $selectedOnlineEvent,
        int $selectedWorkGroup,
        string $searchTerm,
        int $selectedCategory,
        string $selectedTopEventRestriction,
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
                $selectedOnlineEvent,
                $selectedWorkGroup,
                $searchTerm,
                $selectedCategory,
                $selectedTopEventRestriction
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
        string $selectedOnlineEvent,
        int $selectedWorkGroup,
        string $searchTerm,
        int $selectedCategory,
        string $selectedTopEventRestriction
    ): bool
    {
        $metadata = $metadataByUid[$event->getUid()] ?? [
            'onlineEvent' => false,
            'workGroups' => [],
        ];

        if ($selectedOnlineEvent !== '' && (string)(int)$metadata['onlineEvent'] !== $selectedOnlineEvent) {
            return false;
        }

        if ($selectedWorkGroup > 0 && !in_array($selectedWorkGroup, $metadata['workGroups'], true)) {
            return false;
        }

        if ($selectedTopEventRestriction === '2' && !$event->getTopEvent()) {
            return false;
        }

        if ($selectedCategory > 0) {
            $matchesCategory = false;
            foreach ($event->getCategories() ?? [] as $category) {
                if ((int)$category->getUid() === $selectedCategory) {
                    $matchesCategory = true;
                    break;
                }
            }
            if (!$matchesCategory) {
                return false;
            }
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
