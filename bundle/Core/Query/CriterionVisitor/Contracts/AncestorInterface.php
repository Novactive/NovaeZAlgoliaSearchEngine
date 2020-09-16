<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\Contracts;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

interface AncestorInterface
{
    public function visitAncestor(Criterion $criterion, string $indexField, string $additionalOperators): string;
}