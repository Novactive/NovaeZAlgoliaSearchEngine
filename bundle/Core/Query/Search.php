<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query;

use Novactive\Bundle\eZAlgoliaSearchEngine\Core\AttributeGenerator;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\FullTextVisitor;
use Novactive\Bundle\eZAlgoliaSearchEngine\DependencyInjection\Configuration;
use RuntimeException;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\AlgoliaClient;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\FacetBuilderVisitor\FacetBuilderVisitor;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\ResultExtractor\ResultExtractor;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\SortClauseVisitor\SortClauseVisitor;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

final class Search
{
    /**
     * @var AlgoliaClient
     */
    private $client;

    /**
     * @var AttributeGenerator;
     */
    private $attributeGenerator;

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

    /**
     * @var SortClauseVisitor
     */
    private $dispatcherSortClauseVisitor;

    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    public function __construct(
        AlgoliaClient $client,
        AttributeGenerator $attributeGenerator,
        ResultExtractor $resultExtractor,
        FacetBuilderVisitor $dispatcherFacetVisitor,
        CriterionVisitor $dispatcherCriterionVisitor,
        SortClauseVisitor $dispatcherSortClauseVisitor,
        ConfigResolverInterface $configResolver
    ) {
        $this->client = $client;
        $this->attributeGenerator = $attributeGenerator;
        $this->resultExtractor = $resultExtractor;
        $this->dispatcherFacetVisitor = $dispatcherFacetVisitor;
        $this->dispatcherCriterionVisitor = $dispatcherCriterionVisitor;
        $this->dispatcherSortClauseVisitor = $dispatcherSortClauseVisitor;
        $this->configResolver = $configResolver;
    }

    public function execute(Query $query, string $docType, array $languageFilter): SearchResult
    {
        $filters = "doc_type_s:{$docType}";

        if (null !== $query->filter) {
            $filters .= ' AND '.$this->visitFilter($query->filter);
        }
        if (null !== $query->query) {
            $filters .= ' AND '.$this->visitFilter($query->query);
        }

        $queryString = '';
        $restrictedSearchableAttributes = [];

        // Removing the Fulltext criterion from the filters and transforming it to the query string
        if (preg_match('#'.sprintf(FullTextVisitor::placeholder, '(.+)').'#', $filters, $match)) {
            $queryString = $match[1];
            $filters = preg_replace('# AND '.sprintf(FullTextVisitor::placeholder, '.+').'#', '', $filters);
            $restrictedSearchableAttributes = $this->attributeGenerator->getCustomSearchableAttributes(true);
        }

        if (!$languageFilter['useAlwaysAvailable']) {
            $filters .= ' AND ('.implode(
                    ' OR ',
                    array_map(
                        static function ($value) {
                            return 'content_language_codes_ms:"'.$value.'"';
                        },
                        $languageFilter['languages']
                    )
                ).')';
        }

        $requestOptions = [
            'filters' => $filters,
            'attributesToHighlight' => [],
            'offset' => $query->offset,
            'length' => $query->limit,
            'facets' => $this->visitFacetBuilder($query->facetBuilders),
            'restrictSearchableAttributes' => $restrictedSearchableAttributes,
            'attributesToRetrieve' => $this->configResolver->getParameter(
                'attributes_to_retrieve',
                Configuration::NAMESPACE
            )
        ];

        return $this->getExtractedSearchResult(
            $languageFilter['languages'][0],
            $this->visitSortClauses($query->sortClauses),
            $queryString,
            $requestOptions,
            $query->facetBuilders
        );
    }

    public function sendClientRequest(
        string $languageCode,
        ?string $replaicaName = null,
        string $query = '',
        array $requestOptions = []
    ): array {
        return $this->client->getIndex($languageCode, 'search', $replaicaName)->search($query, $requestOptions);
    }

    public function getExtractedSearchResult(
        string $languageCode,
        ?string $replaicaName = null,
        string $query = '',
        array $requestOptions = [],
        array $facetBuilders = []
    ): SearchResult {
        $data = $this->sendClientRequest($languageCode, $replaicaName, $query, $requestOptions);

        return $this->resultExtractor->extract($data, $facetBuilders);
    }

    private function visitFacetBuilder(array $facetBuilders): array
    {
        $facets = [];
        foreach ($facetBuilders as $facetBuilder) {
            $facets[] = $this->dispatcherFacetVisitor->visit($facetBuilder);
        }

        return $facets;
    }

    public function visitFilter(Criterion $criterion): string
    {
        return $this->dispatcherCriterionVisitor->visit($this->dispatcherCriterionVisitor, $criterion);
    }

    private function visitSortClauses(array $sortClauses): ?string
    {
        if (count($sortClauses) > 1) {
            throw new RuntimeException('Only one Sort Clause cab be used to select the sorting replica.');
        }

        if (count($sortClauses) === 0) {
            return null;
        }

        return $this->dispatcherSortClauseVisitor->visit(
            $this->dispatcherSortClauseVisitor,
            $sortClauses[0]
        );
    }
}