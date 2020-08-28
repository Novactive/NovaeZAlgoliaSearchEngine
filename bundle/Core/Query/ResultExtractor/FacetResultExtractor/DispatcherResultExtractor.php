<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\ResultExtractor\FacetResultExtractor;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet;

final class DispatcherResultExtractor implements FacetResultExtractor
{
    /**
     * @var iterable
     */
    private $extractors;

    public function __construct(iterable $extractors = [])
    {
        $this->extractors = $extractors;
    }

    public function supports(FacetBuilder $builder): bool
    {
        return $this->findExtractor($builder) !== null;
    }

    public function extract(FacetBuilder $builder, array $data): Facet
    {
        $extractor = $this->findExtractor($builder);

        if ($extractor === null) {
            throw new NotImplementedException(
                'No result extractor available for: ' . get_class($builder)
            );
        }

        return $extractor->extract($builder, $data);
    }

    private function findExtractor(FacetBuilder $builder): ?FacetResultExtractor
    {
        foreach ($this->extractors as $extractor) {
            if ($extractor->supports($builder)) {
                return $extractor;
            }
        }

        return null;
    }
}
