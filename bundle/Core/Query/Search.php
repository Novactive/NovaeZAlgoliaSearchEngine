<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query;

use Exception;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\AlgoliaClient;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\FacetBuilderVisitor\FacetBuilderVisitor;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\ResultExtractor\ResultExtractor;

final class Search
{
    /**
     * @var AlgoliaClient
     */
    private $client;

    /**
     * @var ResultExtractor
     */
    private $resultExtractor;

    /**
     * @var FacetBuilderVisitor
     */
    private $dispatcherVisitor;

    public function __construct(
        AlgoliaClient $client,
        ResultExtractor $resultExtractor,
        FacetBuilderVisitor $dispatcherVisitor
    ) {
        $this->client = $client;
        $this->resultExtractor = $resultExtractor;
        $this->dispatcherVisitor = $dispatcherVisitor;
    }

    public function execute(Query $query, string $docType, array $languageFilter): SearchResult
    {
        $filters = "doc_type_s:{$docType}";

        // test value
        $filters .= ' AND content_id_i = 57';

        $requestOptions = [
            'filters' => $filters,
            'attributesToHighlight' => [],
            'offset' => 0,
            'length' => $query->limit,
            'facets' => $this->visitFacetBuilder($query)
        ];

        try {
            $data = $this->client->getIndex($languageFilter['languages'][0])->search('', $requestOptions);

            return $this->resultExtractor->extract($data, $query->facetBuilders);

        } catch (Exception $e) {
            throw $e;
        }
    }

    private function visitFacetBuilder(Query $query): array
    {
        $facets = [];
        foreach ($query->facetBuilders as $facetBuilder) {
            $facets[] = $this->dispatcherVisitor->visit($facetBuilder);
        }

        return $facets;
    }
}