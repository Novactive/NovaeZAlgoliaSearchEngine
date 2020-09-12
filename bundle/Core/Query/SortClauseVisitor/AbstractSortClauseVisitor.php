<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\SortClauseVisitor;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use RuntimeException;

abstract class AbstractSortClauseVisitor implements SortClauseVisitor
{
    protected function getDirection(SortClause $sortClause): string
    {
        switch ($sortClause->direction) {
            case Query::SORT_ASC:
                return 'asc';
            case Query::SORT_DESC:
                return 'desc';
            default:
                throw new RuntimeException('Invalid sort direction: ' . $sortClause->direction);
        }
    }
}
