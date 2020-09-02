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
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\CriterionVisitor;
use RuntimeException;

final class PriorityVisitor implements CriterionVisitor
{
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
        if (\in_array($criterion->operator, [Criterion\Operator::EQ, Criterion\Operator::IN], true)) {
            $values = array();
            foreach ($criterion->value as $value) {
                $values[] = $additionalOperators.self::INDEX_FIELD.'='.$value;
            }

            return '('.implode('NOT ' === $additionalOperators ? ' AND ' : ' OR ', $values).')';
        }

        if ($criterion->operator === Criterion\Operator::BETWEEN) {
            if (2 !== count($criterion->value)) {
                throw new RuntimeException("Unsupported number of values for {$criterion->operator} operator");
            }

            return $additionalOperators.self::INDEX_FIELD.':'.$criterion->value[0].' TO '.$criterion->value[1];
        }

        return $additionalOperators.self::INDEX_FIELD.' '.$criterion->operator.' '.$criterion->value[0];
    }
}
