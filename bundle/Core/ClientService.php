<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core;

use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\Search;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;

final class ClientService
{
    /**
     * @var Search
     */
    private $contentSearchService;

    /**
     * @var Search
     */
    private $locationSearchService;

    public function __construct(Search $contentSearch, Search $locationSearch)
    {
        $this->contentSearchService = $contentSearch;
        $this->locationSearchService = $locationSearch;
    }

    public function contentSearch(
        string $languageCode,
        ?string $replaicaName = null,
        string $query = '',
        array $requestOptions = [],
        array $facetBuilders = []
    ): SearchResult {
        $requestOptions['filters'] .= ' AND doc_type_s:content';

        return $this->contentSearchService->getExtractedSearchResult(
            $languageCode,
            $replaicaName,
            $query,
            $requestOptions,
            $facetBuilders
        );
    }

    public function locationSearch(
        string $languageCode,
        ?string $replaicaName = null,
        string $query = '',
        array $requestOptions = [],
        array $facetBuilders = []
    ): SearchResult {
        $requestOptions['filters'] .= ' AND doc_type_s:location';

        return $this->locationSearchService->getExtractedSearchResult(
            $languageCode,
            $replaicaName,
            $query,
            $requestOptions,
            $facetBuilders
        );
    }

    public function rawSearch(
        string $languageCode,
        ?string $replaicaName = null,
        string $query = '',
        array $requestOptions = []
    ): array {
        return $this->contentSearchService->sendClientRequest($languageCode, $replaicaName, $query, $requestOptions);
    }
}