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

interface SortClauseVisitor
{
    public function supports(SortClause $sortClause): bool;

    public function visit(SortClauseVisitor $dispatcher, SortClause $sortClause): string;
}
