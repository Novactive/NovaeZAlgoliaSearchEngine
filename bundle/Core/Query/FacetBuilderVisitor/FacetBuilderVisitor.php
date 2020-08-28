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

interface FacetBuilderVisitor
{
    public function supports(FacetBuilder $builder): bool;

    public function visit(FacetBuilder $builder): string;
}
