<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use RuntimeException;

final class LogicalNotVisitor implements CriterionVisitor
{
    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\LogicalNot;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        /** @var Criterion\LogicalNot $criterion */
        if (count($criterion->criteria) !== 1) {
            throw new RuntimeException('Invalid aggregation in LogicalNot criterion.');
        }

        if ($criterion->criteria[0] instanceof Criterion\LogicalAnd) {
            // the reference for checking out the way to manage that:
            // https://www.algolia.com/doc/api-reference/api-parameters/filters/#boolean-operators
            $docRef = 'Check out the reference of Algolia boolean operators: ';
            $docRef .= 'https://www.algolia.com/doc/api-reference/api-parameters/filters/#boolean-operators';
            throw new RuntimeException('AND operator cannot be inside LogicalNot criterion. '.$docRef);
        }

        return $dispatcher->visit($dispatcher, $criterion->criteria[0], 'NOT ');
    }
}
