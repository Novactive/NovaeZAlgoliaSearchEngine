<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\Location;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\Contracts\CommonVisitor;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\CriterionVisitor;

final class PriorityVisitor implements CriterionVisitor
{
    use CommonVisitor;

    private const INDEX_FIELD = 'priority_i';

    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\Location\Priority &&
               \in_array(
                   $criterion->operator,
                   [
                       Criterion\Operator::EQ,
                       Criterion\Operator::IN,
                       Criterion\Operator::LT,
                       Criterion\Operator::LTE,
                       Criterion\Operator::GT,
                       Criterion\Operator::GTE,
                       Criterion\Operator::BETWEEN
                   ],
                   true
               );
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        return $this->visitWithOperators($criterion, $additionalOperators, self::INDEX_FIELD);
    }
}
