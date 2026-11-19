<?php

declare(strict_types=1);

namespace HGON\HgonTemplate\Middleware;

use Doctrine\DBAL\ParameterType;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;

/**
 * Normalizes the letter case of editorial species shortlinks.
 *
 * The species shortlinks are regular TYPO3 pages of type "External URL" below
 * the dedicated shortlink folder. This allows editors to maintain short URLs
 * such as `/blaumeise` without requiring a code change for every species.
 *
 * TYPO3 v13 resolves page slugs case-sensitively. Consequently, incorrectly
 * capitalized URLs printed on existing QR codes (for example `/Blaumeise`)
 * would otherwise result in a 404 response. If a case-insensitive match exists,
 * this middleware issues a permanent redirect to the slug stored in TYPO3.
 *
 * To avoid changing the routing behaviour of unrelated pages, normalization is
 * deliberately limited to:
 *
 * - GET and HEAD requests;
 * - the HGON main site;
 * - single-segment paths directly below the site root;
 * - visible external URL pages below the configured shortlink PID.
 *
 * Query parameters are retained because only the path component of the request
 * URI is replaced.
 */
final readonly class SpeciesShortlinkCaseNormalization implements MiddlewareInterface
{
    /** Site configuration identifier of the HGON main website. */
    private const SITE_IDENTIFIER = 'hgon_de';

    /** PID containing the editor-managed species shortlink pages. */
    private const SHORTLINK_PID = 435;

    /** TYPO3 doktype value for pages of type "External URL". */
    private const EXTERNAL_URL_DOKTYPE = 3;

    /**
     * @param ConnectionPool $connectionPool Used to look up canonical page slugs.
     */
    public function __construct(
        private ConnectionPool $connectionPool,
    ) {}

    /**
     * Redirects a differently-cased species shortlink to its canonical slug.
     *
     * Requests that do not belong to the narrowly defined shortlink use case
     * are passed to the remaining TYPO3 middleware stack unchanged.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return $handler->handle($request);
        }

        $site = $request->getAttribute('site');
        if (!$site instanceof SiteInterface || $site->getIdentifier() !== self::SITE_IDENTIFIER) {
            return $handler->handle($request);
        }

        $requestedPath = rawurldecode($request->getUri()->getPath());

        // Species shortlinks consist of exactly one path segment. Lowercase
        // paths are already canonical and can be resolved normally by TYPO3.
        if (!preg_match('#^/[^/]+$#u', $requestedPath)
            || $requestedPath === mb_strtolower($requestedPath, 'UTF-8')
        ) {
            return $handler->handle($request);
        }

        $canonicalPath = $this->findCanonicalPath($requestedPath);
        if ($canonicalPath === null || $canonicalPath === $requestedPath) {
            return $handler->handle($request);
        }

        $targetUri = $request->getUri()->withPath($canonicalPath);

        // Using the original URI as the base deliberately preserves its query.
        return (new RedirectResponse($targetUri, 301))
            ->withHeader('X-Redirect-By', 'HGON species shortlink case normalization');
    }

    /**
     * Finds the canonical slug for a case-insensitive shortlink match.
     *
     * TYPO3's QueryBuilder quotes the first argument passed to `eq()` as a
     * field name. The LOWER() expression therefore uses `comparison()` while
     * the requested path remains safely bound as a named parameter.
     *
     * @return string|null Canonical root-relative slug or null when no active
     *     editorial species shortlink matches the requested path.
     * @throws \Doctrine\DBAL\Exception
     */
    private function findCanonicalPath(string $requestedPath): ?string
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $row = $queryBuilder
            ->select('slug')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter(self::SHORTLINK_PID, ParameterType::INTEGER)
                ),
                $queryBuilder->expr()->eq(
                    'doktype',
                    $queryBuilder->createNamedParameter(self::EXTERNAL_URL_DOKTYPE, ParameterType::INTEGER)
                ),
                $queryBuilder->expr()->eq(
                    'hidden',
                    $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
                ),
                $queryBuilder->expr()->comparison(
                    'LOWER(' . $queryBuilder->quoteIdentifier('slug') . ')',
                    '=',
                    $queryBuilder->createNamedParameter(mb_strtolower($requestedPath, 'UTF-8'))
                )
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        if (!is_array($row) || !is_string($row['slug'] ?? null) || $row['slug'] === '') {
            return null;
        }

        return str_starts_with($row['slug'], '/') ? $row['slug'] : '/' . $row['slug'];
    }
}
