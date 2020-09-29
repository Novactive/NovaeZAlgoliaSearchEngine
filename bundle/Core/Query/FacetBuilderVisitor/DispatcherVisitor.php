<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\FacetBuilderVisitor;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;

final class DispatcherVisitor implements FacetBuilderVisitor
{
    /**
     * @var iterable
     */
    private $visitors;

    public function __construct(iterable $visitors)
    {
        $this->visitors = $visitors;
    }

    public function supports(FacetBuilder $builder): bool
    {
        return $this->findVisitor($builder) !== null;
    }

    public function visit(FacetBuilder $builder): string
    {
        $visitor = $this->findVisitor($builder);

        if ($visitor === null) {
            throw new NotImplementedException(
                'No visitor available for: ' . \get_class($builder)
            );
        }

        return $visitor->visit($builder);
    }

    private function findVisitor(FacetBuilder $builder): ?FacetBuilderVisitor
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->supports($builder)) {
                return $visitor;
            }
        }

        return null;
    }
}
