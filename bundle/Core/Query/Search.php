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
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

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
    private $dispatcherFacetVisitor;

    /**
     * @var CriterionVisitor
     */
    private $dispatcherCriterionVisitor;

    public function __construct(
        AlgoliaClient $client,
        ResultExtractor $resultExtractor,
        FacetBuilderVisitor $dispatcherFacetVisitor,
        CriterionVisitor $dispatcherCriterionVisitor
    ) {
        $this->client = $client;
        $this->resultExtractor = $resultExtractor;
        $this->dispatcherFacetVisitor = $dispatcherFacetVisitor;
        $this->dispatcherCriterionVisitor = $dispatcherCriterionVisitor;
    }

    public function execute(Query $query, string $docType, array $languageFilter): SearchResult
    {
        $filters = "doc_type_s:{$docType}";

        if (null !== $query->filter) {
            $filters .= ' AND '.$this->visitFilter($query->filter);
            //$filters .= ' AND short_title_is_empty_b:true';
        }

        dump($filters);

        $requestOptions = [
            'filters' => $filters,
            'attributesToHighlight' => [],
            'offset' => 0,
            'length' => $query->limit,
            'facets' => $this->visitFacetBuilder($query),
//            'aroundLatLng' => '37.7512306, -122.4584587',
//            'aroundRadius' => 2000
        ];

        try {
            $data = $this->client->getIndex($languageFilter['languages'][0])->search('', $requestOptions);

            return $this->resultExtractor->extract($data, $query->facetBuilders);

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function visitFacetBuilder(Query $query): array
    {
        $facets = [];
        foreach ($query->facetBuilders as $facetBuilder) {
            $facets[] = $this->dispatcherFacetVisitor->visit($facetBuilder);
        }

        return $facets;
    }

    public function visitFilter(Criterion $criterion): string
    {
        return $this->dispatcherCriterionVisitor->visit($this->dispatcherCriterionVisitor, $criterion);
    }
}