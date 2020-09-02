<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

final class DispatcherVisitor implements CriterionVisitor
{
    /**
     * @var iterable
     */
    private $visitors;

    public function __construct(iterable $visitors = [])
    {
        $this->visitors = $visitors;
    }

    public function supports(Criterion $criterion): bool
    {
        return $this->findVisitor($criterion) !== null;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        $visitor = $this->findVisitor($criterion);
        if ($visitor === null) {
            throw new NotImplementedException(
                'No visitor available for: ' . get_class($criterion) . ' with operator ' . $criterion->operator
            );
        }

        return $visitor->visit($dispatcher, $criterion, $additionalOperators);
    }

    private function findVisitor(Criterion $criterion): ?CriterionVisitor
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->supports($criterion)) {
                return $visitor;
            }
        }

        return null;
    }
}
