<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Routing\Aspect;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;

/**
 * Resolves bird slugs stored in the central data sysfolder outside the site tree.
 */
final class SpeciesSlugMapper extends PersistedAliasMapper
{
    protected function createQueryBuilder(): QueryBuilder
    {
        $queryBuilder = parent::createQueryBuilder();
        $queryBuilder->andWhere(
            $queryBuilder->expr()->eq(
                'record_type',
                $queryBuilder->createNamedParameter('bird', ParameterType::STRING)
            )
        );

        return $queryBuilder;
    }
}
