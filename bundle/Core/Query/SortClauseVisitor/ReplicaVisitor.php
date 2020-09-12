<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\SortClauseVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\SortClause\Replica;

final class ReplicaVisitor extends AbstractSortClauseVisitor
{
    public function supports(SortClause $sortClause): bool
    {
        return $sortClause instanceof Replica;
    }

    public function visit(SortClauseVisitor $visitor, SortClause $sortClause): string
    {
        return $sortClause->target;
    }
}