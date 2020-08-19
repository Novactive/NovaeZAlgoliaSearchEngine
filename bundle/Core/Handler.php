<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Search\Handler as SearchHandlerInterface;
use eZ\Publish\SPI\Search\Capable;

class Handler implements SearchHandlerInterface, Capable
{
    private AlgoliaClient $client;

    public function __construct(AlgoliaClient $algoliaClient) {
        $this->client = $algoliaClient;
    }

    public function supports(int $capabilityFlag): bool
    {
        switch ($capabilityFlag) {
            case SearchService::CAPABILITY_SCORING:
            case SearchService::CAPABILITY_FACETS:
            case SearchService::CAPABILITY_CUSTOM_FIELDS:
                //case SearchService::CAPABILITY_SPELLCHECK:
                //case SearchService::CAPABILITY_HIGHLIGHT:
                //case SearchService::CAPABILITY_SUGGEST:
            case SearchService::CAPABILITY_ADVANCED_FULLTEXT:
                return true;
            default:
                return false;
        }
    }

    public function findContent(Query $query, array $languageFilter = [])
    {
    }

    public function findSingle(Criterion $filter, array $languageFilter = [])
    {
    }

    public function findLocations(LocationQuery $query, array $languageFilter = [])
    {
    }

    public function suggest($prefix, $fieldPaths = [], $limit = 10, Criterion $filter = null): void
    {
    }

    public function indexContent(Content $content): void
    {
    }

    public function indexLocation(Location $location): void
    {
    }

    public function deleteContent($contentId, $versionId = null): void
    {
    }

    public function deleteLocation($locationId, $contentId): void
    {
    }

    public function purgeIndex(): void
    {
    }
}