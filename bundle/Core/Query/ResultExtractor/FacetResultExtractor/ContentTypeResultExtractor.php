<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\ResultExtractor\FacetResultExtractor;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\ContentTypeFacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet;
use eZ\Publish\API\Repository\Values\Content\Search\Facet\ContentTypeFacet;

final class ContentTypeResultExtractor extends AbstractTermsResultExtractor
{
    public function supports(FacetBuilder $builder): bool
    {
        return $builder instanceof ContentTypeFacetBuilder;
    }

    public function extract(FacetBuilder $builder, array $data): Facet
    {
        $facet = new ContentTypeFacet();
        $facet->name = $builder->name;
        $facet->entries = $this->extractEntries($data);

        return $facet;
    }
}
