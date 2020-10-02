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
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\FacetBuilderVisitor\ContentTypeVisitor;

final class ContentTypeResultExtractor implements FacetResultExtractor
{
    public function supports(FacetBuilder $builder): bool
    {
        return $builder instanceof ContentTypeFacetBuilder;
    }

    public function extract(FacetBuilder $builder, array $data): Facet
    {
        $facet = new ContentTypeFacet();
        $facet->name = $builder->name ?? ContentTypeVisitor::FACET_ATTRIBUTE;
        $facet->entries = $data[ContentTypeVisitor::FACET_ATTRIBUTE];

        return $facet;
    }
}
