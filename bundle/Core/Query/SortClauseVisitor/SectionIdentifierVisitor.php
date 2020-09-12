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

final class SectionIdentifierVisitor extends AbstractSortClauseVisitor
{
    public function supports(SortClause $sortClause): bool
    {
        return $sortClause instanceof SortClause\SectionIdentifier;
    }

    public function visit(SortClauseVisitor $visitor, SortClause $sortClause): string
    {
        return 'sort_by_section_identifier_id_'.$this->getDirection($sortClause);
    }
}
