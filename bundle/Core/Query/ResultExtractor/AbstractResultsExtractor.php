<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\ResultExtractor;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\ValueObject;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\ResultExtractor\FacetResultExtractor\FacetResultExtractor;

abstract class AbstractResultsExtractor implements ResultExtractor
{
    public const MATCHED_TRANSLATION_FIELD = 'meta_indexed_language_code_s';

    /** @var FacetResultExtractor */
    private $dispatcherResultExtractor;

    /** @var bool */
    private $skipMissingValueObject;

    public function __construct(FacetResultExtractor $dispatcherResultExtractor, bool $skipMissingValueObject = true)
    {
        $this->dispatcherResultExtractor = $dispatcherResultExtractor;
        $this->skipMissingValueObject = $skipMissingValueObject;
    }

    final public function extract(array $data, iterable $facetBuilders): SearchResult
    {
        $result = new SearchResult();
        $result->totalCount = $data['nbHits'];

        foreach ($this->extractSearchHits($data['hits']) as $searchHit) {
            if ($searchHit === null) {
                --$result->totalCount;
                continue;
            }

            $result->searchHits[] = $searchHit;
        }

        if (isset($data['facets'])) {
            $result->facets = $this->extractFacets($facetBuilders, $data['facets']);
        }

        return $result;
    }

    abstract protected function loadValueObject(array $document): ValueObject;

    private function extractSearchHits(array $data): iterable
    {
        if (empty($data)) {
            yield from [];
        }

        foreach ($data as $hit) {
            try {
                $searchResultHit = new SearchHit();
                $searchResultHit->valueObject = $this->loadValueObject($hit);
                // TODO: Adapt \eZ\Publish\API\Repository\Tests\SearchServiceTranslationLanguageFallbackTest::getIndexesToMatchData
                //$resultHit->index = $hit['_index'];
                // unrecognized field
                //$searchResultHit->score = $hit['_score'];
                $searchResultHit->matchedTranslation = $hit[self::MATCHED_TRANSLATION_FIELD];

                yield $searchResultHit;
            } catch (NotFoundException $e) {
                if (!$this->skipMissingValueObject) {
                    throw $e;
                }

                yield null;
            }
        }
    }

    private function extractFacets(iterable $facetBuilders, array $data): array
    {
        $facets = [];
        foreach ($facetBuilders as $facetBuilder) {
            $facets[] = $this->dispatcherResultExtractor->extract($facetBuilder, $data);
        }

        return $facets;
    }
}
