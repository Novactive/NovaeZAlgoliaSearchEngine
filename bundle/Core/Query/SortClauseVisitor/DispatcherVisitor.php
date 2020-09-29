<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\SortClauseVisitor;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

final class DispatcherVisitor implements SortClauseVisitor
{
    /**
     * @var iterable
     */
    private $visitors;

    public function __construct(iterable $visitors = [])
    {
        $this->visitors = $visitors;
    }

    public function supports(SortClause $sortClause): bool
    {
        return $this->findVisitor($sortClause) !== null;
    }

    public function visit(SortClauseVisitor $dispatcher, SortClause $sortClause): string
    {
        $visitor = $this->findVisitor($sortClause);
        if ($visitor === null) {
            throw new NotImplementedException('No visitor available for: '.\get_class($sortClause));
        }

        return $visitor->visit($dispatcher, $sortClause);
    }

    private function findVisitor(SortClause $sortClause): ?SortClauseVisitor
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->supports($sortClause)) {
                return $visitor;
            }
        }

        return null;
    }
}
