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

final class LogicalOrVisitor implements CriterionVisitor
{
    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\LogicalOr;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        /** @var Criterion\LogicalOr $criterion */
        if (empty($criterion->criteria)) {
            throw new RuntimeException('Invalid aggregation in LogicalOr criterion.');
        }

        $subCriteria = array_map(
            static function ($value) use ($dispatcher, $additionalOperators) {
                if ($value instanceof Criterion\LogicalOr || $value instanceof Criterion\LogicalAnd) {
                    // the reference for checking out the way to manage that:
                    // https://www.algolia.com/doc/api-reference/api-parameters/filters/#boolean-operators
                    $docRef = 'Check out the reference of Algolia boolean operators: ';
                    $docRef .= 'https://www.algolia.com/doc/api-reference/api-parameters/filters/#boolean-operators';
                    throw new RuntimeException('AND/OR operator cannot be inside LogicalOr criterion. '.$docRef);
                }
                if ($value instanceof Criterion\FullText) {
                    throw new RuntimeException(
                        "FullText Criterion cannot be inside LogicalOr operator ".
                        "because it's moved to the query string of the Algolia request which is performed anyway."
                    );
                }

                return $dispatcher->visit($dispatcher, $value, $additionalOperators);
            },
            $criterion->criteria
        );

        if (\count($subCriteria) === 1) {
            return reset($subCriteria);
        }

        return '('.implode('NOT ' === $additionalOperators ? ' AND ' : ' OR ', $subCriteria).')';
    }
}
