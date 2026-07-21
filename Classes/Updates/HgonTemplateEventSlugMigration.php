<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Updates;

use Doctrine\DBAL\ParameterType;
use RuntimeException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Generates the slugs required by the sf_event_mgt detail route enhancer.
 *
 * Existing slugs are deliberately preserved.
 */
#[UpgradeWizard('hgonTemplateEventSlugMigration')]
final class HgonTemplateEventSlugMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const TABLE = 'tx_sfeventmgt_domain_model_event';
    private const LEGACY_TABLE = 'tx_rkwevents_domain_model_event';
    private const SLUG_FIELD = 'slug';
    private const TARGET_PID = 37;
    private const LEGACY_PID = 42;
    private const DETAIL_PAGE_UID = 40;
    private const CURRENT_DETAIL_PAGE_SLUG = '/veranstaltungen/veranstaltung';
    private const LEGACY_DETAIL_PAGE_SLUG = '/veranstaltungen/event';

    public function getTitle(): string
    {
        return 'HGON Template: URL-Slugs der migrierten Veranstaltungen erzeugen';
    }

    public function getDescription(): string
    {
        return 'Übernimmt das bisherige URL-Schema aus Titel und alter Event-UID für migrierte Veranstaltungen '
            . 'und stellt den Detailseitenpfad /veranstaltungen/event wieder her. Bereits vorhandene Event-Slugs '
            . 'und abweichend konfigurierte Seitenpfade bleiben unverändert.';
    }

    public function executeUpdate(): bool
    {
        $events = $this->findEventsWithoutSlug();
        $slugHelper = null;
        $legacyUidMap = [];
        if ($events !== []) {
            $fieldConfig = $GLOBALS['TCA'][self::TABLE]['columns'][self::SLUG_FIELD]['config'] ?? null;
            if (!is_array($fieldConfig)) {
                throw new RuntimeException('Die TCA-Konfiguration für Event-Slugs ist nicht verfügbar.');
            }

            $slugHelper = GeneralUtility::makeInstance(
                SlugHelper::class,
                self::TABLE,
                self::SLUG_FIELD,
                $fieldConfig
            );
            $legacyUidMap = $this->findLegacyUidMap();
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(self::TABLE);

        $connection->transactional(function (Connection $connection) use ($events, $slugHelper, $legacyUidMap): void {
            foreach ($events as $event) {
                $legacyKey = $this->buildLegacyKey(
                    (string)$event['title'],
                    (int)$event['startdate'],
                    (int)$event['sys_language_uid']
                );
                $legacyUid = $legacyUidMap[$legacyKey] ?? 0;
                if ($legacyUid <= 0) {
                    throw new RuntimeException(sprintf(
                        'Für die migrierte Veranstaltung %d wurde kein eindeutiger Legacy-Datensatz gefunden.',
                        (int)$event['uid']
                    ));
                }

                $slugRecord = $event;
                $slugRecord['title'] = sprintf('%s-%d', rtrim((string)$event['title']), $legacyUid);
                $slug = $slugHelper->generate($slugRecord, (int)$event['pid']);
                if ($slug === '') {
                    throw new RuntimeException(sprintf(
                        'Für die Veranstaltung %d konnte kein Slug erzeugt werden.',
                        (int)$event['uid']
                    ));
                }

                $state = RecordStateFactory::forName(self::TABLE)->fromArray($event);
                if (!$slugHelper->isUniqueInSite($slug, $state)) {
                    throw new RuntimeException(sprintf(
                        'Der Legacy-Slug %s der Veranstaltung %d wird bereits verwendet.',
                        $slug,
                        (int)$event['uid']
                    ));
                }

                $queryBuilder = $connection->createQueryBuilder();
                $affectedRows = $queryBuilder
                    ->update(self::TABLE)
                    ->set(self::SLUG_FIELD, $slug, true, ParameterType::STRING)
                    ->where(
                        $queryBuilder->expr()->eq(
                            'uid',
                            $queryBuilder->createNamedParameter((int)$event['uid'], ParameterType::INTEGER)
                        ),
                        $queryBuilder->expr()->eq(
                            'pid',
                            $queryBuilder->createNamedParameter(self::TARGET_PID, ParameterType::INTEGER)
                        ),
                        $queryBuilder->expr()->eq(
                            'deleted',
                            $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
                        ),
                        $queryBuilder->expr()->or(
                            $queryBuilder->expr()->eq(
                                self::SLUG_FIELD,
                                $queryBuilder->createNamedParameter('', ParameterType::STRING)
                            ),
                            $queryBuilder->expr()->isNull(self::SLUG_FIELD),
                            $queryBuilder->expr()->like(
                                self::SLUG_FIELD,
                                $queryBuilder->createNamedParameter(':dcValue%', ParameterType::STRING)
                            )
                        )
                    )
                    ->executeStatement();

                if ($affectedRows !== 1) {
                    throw new RuntimeException(sprintf(
                        'Der Slug der Veranstaltung %d konnte nicht eindeutig gespeichert werden.',
                        (int)$event['uid']
                    ));
                }
            }

            $this->migrateDetailPageSlug($connection);
        });

        return true;
    }

    public function updateNecessary(): bool
    {
        return $this->findEventsWithoutSlug() !== [] || $this->detailPageSlugNeedsMigration();
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
            HgonTemplateRkwEventsDataMigration::class,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function findEventsWithoutSlug(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->select('*')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter(self::TARGET_PID, ParameterType::INTEGER)
                ),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq(
                        self::SLUG_FIELD,
                        $queryBuilder->createNamedParameter('', ParameterType::STRING)
                    ),
                    $queryBuilder->expr()->isNull(self::SLUG_FIELD),
                    $queryBuilder->expr()->like(
                        self::SLUG_FIELD,
                        $queryBuilder->createNamedParameter(':dcValue%', ParameterType::STRING)
                    )
                )
            )
            ->orderBy('sorting')
            ->addOrderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * Maps the legacy title/start/language tuple to its former UID. If the old
     * source contains an exact duplicate, the last migrated record wins just
     * as it did in the original data migration.
     *
     * @return array<string, int>
     */
    private function findLegacyUidMap(): array
    {
        if (!$this->tableExists(self::LEGACY_TABLE)) {
            throw new RuntimeException(
                'Die alte RKW-Eventtabelle fehlt; langlebige Event-URLs können nicht mehr rekonstruiert werden.'
            );
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::LEGACY_TABLE);
        $legacyEvents = $queryBuilder
            ->select('uid', 'title', 'start', 'sys_language_uid')
            ->from(self::LEGACY_TABLE)
            ->where(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter(self::LEGACY_PID, ParameterType::INTEGER)
                ),
                $queryBuilder->expr()->eq(
                    'deleted',
                    $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
                )
            )
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();

        $result = [];
        foreach ($legacyEvents as $legacyEvent) {
            $result[$this->buildLegacyKey(
                (string)$legacyEvent['title'],
                (int)$legacyEvent['start'],
                (int)$legacyEvent['sys_language_uid']
            )] = (int)$legacyEvent['uid'];
        }

        return $result;
    }

    private function buildLegacyKey(string $title, int $startDate, int $languageId): string
    {
        return hash('sha256', $title . "\0" . $startDate . "\0" . $languageId);
    }

    private function detailPageSlugNeedsMigration(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');

        return (bool)$queryBuilder
            ->count('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter(self::DETAIL_PAGE_UID, ParameterType::INTEGER)
                ),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq(
                        'slug',
                        $queryBuilder->createNamedParameter(self::CURRENT_DETAIL_PAGE_SLUG, ParameterType::STRING)
                    ),
                    $queryBuilder->expr()->like(
                        'slug',
                        $queryBuilder->createNamedParameter(':dcValue%', ParameterType::STRING)
                    )
                ),
                $queryBuilder->expr()->eq(
                    'deleted',
                    $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
                )
            )
            ->executeQuery()
            ->fetchOne();
    }

    private function migrateDetailPageSlug(Connection $connection): void
    {
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder
            ->update('pages')
            ->set('slug', self::LEGACY_DETAIL_PAGE_SLUG, true, ParameterType::STRING)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter(self::DETAIL_PAGE_UID, ParameterType::INTEGER)
                ),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq(
                        'slug',
                        $queryBuilder->createNamedParameter(self::CURRENT_DETAIL_PAGE_SLUG, ParameterType::STRING)
                    ),
                    $queryBuilder->expr()->like(
                        'slug',
                        $queryBuilder->createNamedParameter(':dcValue%', ParameterType::STRING)
                    )
                ),
                $queryBuilder->expr()->eq(
                    'deleted',
                    $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
                )
            )
            ->executeStatement();
    }

    private function tableExists(string $table): bool
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($table);

        return in_array($table, $connection->createSchemaManager()->listTableNames(), true);
    }
}
