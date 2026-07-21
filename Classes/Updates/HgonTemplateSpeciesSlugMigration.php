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

#[UpgradeWizard('hgonTemplateSpeciesSlugMigration')]
final class HgonTemplateSpeciesSlugMigration implements UpgradeWizardInterface, RepeatableInterface
{
    private const TABLE = 'tx_hgonspecies_domain_model_species';
    private const SLUG_FIELD = 'slug';
    private const DETAIL_PAGE_UID = 434;
    private const CURRENT_DETAIL_PAGE_SLUG = '/unsere-arbeit/voegel/artenliste/detail';
    private const LEGACY_DETAIL_PAGE_SLUG = '/unsere-arbeit/voegel/artenliste/art';

    public function getTitle(): string
    {
        return 'HGON Template: URL-Slugs der Artenliste erzeugen';
    }

    public function getDescription(): string
    {
        return 'Erzeugt eindeutige URL-Slugs aus den Artnamen und stellt für die Vogel-Detailseite '
            . 'den langlebigen Pfad /unsere-arbeit/voegel/artenliste/art wieder her. Vorhandene Slugs '
            . 'und abweichend konfigurierte Seitenpfade bleiben unverändert.';
    }

    public function executeUpdate(): bool
    {
        if (!$this->slugColumnExists()) {
            throw new RuntimeException('Die Datenbankspalte für Arten-Slugs fehlt. Bitte zuerst das Datenbankschema aktualisieren.');
        }
        $speciesRecords = $this->findRecordsWithoutSlug();
        $fieldConfig = $GLOBALS['TCA'][self::TABLE]['columns'][self::SLUG_FIELD]['config'] ?? null;
        if ($speciesRecords !== [] && !is_array($fieldConfig)) {
            throw new RuntimeException('Die TCA-Konfiguration für Arten-Slugs ist nicht verfügbar.');
        }

        $slugHelper = is_array($fieldConfig)
            ? GeneralUtility::makeInstance(SlugHelper::class, self::TABLE, self::SLUG_FIELD, $fieldConfig)
            : null;
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE);

        $connection->transactional(function (Connection $connection) use ($speciesRecords, $slugHelper): void {
            foreach ($speciesRecords as $species) {
                $slug = $slugHelper->generate($species, (int)$species['pid']);
                $state = RecordStateFactory::forName(self::TABLE)->fromArray($species);
                if (!$slugHelper->isUniqueInPid($slug, $state)) {
                    $slugRecord = $species;
                    $slugRecord['name'] = rtrim((string)$species['name']) . '-' . (int)$species['uid'];
                    $slug = $slugHelper->generate($slugRecord, (int)$species['pid']);
                }
                if ($slug === '' || !$slugHelper->isUniqueInPid($slug, $state)) {
                    throw new RuntimeException(sprintf(
                        'Für die Art %d konnte kein eindeutiger Slug erzeugt werden.',
                        (int)$species['uid']
                    ));
                }

                $queryBuilder = $connection->createQueryBuilder();
                $affectedRows = $queryBuilder
                    ->update(self::TABLE)
                    ->set(self::SLUG_FIELD, $slug, true, ParameterType::STRING)
                    ->where(
                        $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter((int)$species['uid'], ParameterType::INTEGER)),
                        $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                        $this->emptySlugConstraint($queryBuilder)
                    )
                    ->executeStatement();
                if ($affectedRows !== 1) {
                    throw new RuntimeException(sprintf('Der Slug der Art %d konnte nicht gespeichert werden.', (int)$species['uid']));
                }
            }

            $queryBuilder = $connection->createQueryBuilder();
            $queryBuilder
                ->update('pages')
                ->set('slug', self::LEGACY_DETAIL_PAGE_SLUG, true, ParameterType::STRING)
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter(self::DETAIL_PAGE_UID, ParameterType::INTEGER)),
                    $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter(self::CURRENT_DETAIL_PAGE_SLUG, ParameterType::STRING)),
                    $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
                )
                ->executeStatement();
        });

        return true;
    }

    public function updateNecessary(): bool
    {
        return !$this->slugColumnExists()
            || $this->findRecordsWithoutSlug() !== []
            || $this->detailPageSlugNeedsMigration();
    }

    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }

    private function findRecordsWithoutSlug(): array
    {
        if (!$this->slugColumnExists()) {
            return [];
        }
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->select('*')
            ->from(self::TABLE)
            ->where($this->emptySlugConstraint($queryBuilder))
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    private function detailPageSlugNeedsMigration(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');

        return (bool)$queryBuilder
            ->count('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter(self::DETAIL_PAGE_UID, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter(self::CURRENT_DETAIL_PAGE_SLUG, ParameterType::STRING)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->executeQuery()
            ->fetchOne();
    }

    private function emptySlugConstraint($queryBuilder)
    {
        return $queryBuilder->expr()->or(
            $queryBuilder->expr()->eq(self::SLUG_FIELD, $queryBuilder->createNamedParameter('', ParameterType::STRING)),
            $queryBuilder->expr()->isNull(self::SLUG_FIELD),
            $queryBuilder->expr()->like(self::SLUG_FIELD, $queryBuilder->createNamedParameter(':dcValue%', ParameterType::STRING))
        );
    }

    private function slugColumnExists(): bool
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE);

        return $connection->createSchemaManager()->tablesExist([self::TABLE])
            && $connection->createSchemaManager()->introspectTable(self::TABLE)->hasColumn(self::SLUG_FIELD);
    }
}
