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
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

final class LogicalAndVisitor implements CriterionVisitor
{
    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\LogicalAnd;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        /** @var Criterion\LogicalAnd $criterion */
        if (empty($criterion->criteria)) {
            throw new InvalidArgumentException('criterion', 'Invalid aggregation in LogicalAnd criterion.');
        }

        $subCriteria = array_map(
            static function ($value) use ($dispatcher, $additionalOperators) {
                return $dispatcher->visit($dispatcher, $value, $additionalOperators);
            },
            $criterion->criteria
        );

        if (\count($subCriteria) === 1) {
            return reset($subCriteria);
        }

        return implode('NOT ' === $additionalOperators ? ' OR ' : ' AND ', $subCriteria);
    }
}
