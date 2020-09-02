<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\FacetBuilderVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\ContentTypeFacetBuilder;

final class ContentTypeVisitor extends AbstractTermsVisitor
{
    public function supports(FacetBuilder $builder): bool
    {
        return $builder instanceof ContentTypeFacetBuilder;
    }

    protected function getTargetField(FacetBuilder $builder): string
    {
        return 'content_type_identifier_s';
    }
}
