<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\Contracts;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

trait CommonVisitor
{
    private function visitValues(array $values, string $comparison, string $additionalOperators): string
    {
        return '('.implode(
                'NOT ' === $additionalOperators ? ' AND ' : ' OR ',
                array_map(
                    static function ($value) use ($additionalOperators, $comparison) {
                        return $additionalOperators.sprintf($comparison, $value);
                    },
                    $values
                )
            ).')';
    }

    private function visitWithOperators(Criterion $criterion, string $additionalOperators, string $indexField): string
    {
        if (\in_array($criterion->operator, [Criterion\Operator::EQ, Criterion\Operator::IN], true)) {
            $values = array();
            foreach ($criterion->value as $value) {
                $values[] = $additionalOperators.$indexField.'='.$value;
            }

            return '('.implode('NOT ' === $additionalOperators ? ' AND ' : ' OR ', $values).')';
        }

        if ($criterion->operator === Criterion\Operator::BETWEEN) {
            if (2 !== \count($criterion->value)) {
                throw new InvalidArgumentException(
                    'value',
                    "Unsupported number of values for {$criterion->operator} operator"
                );
            }

            return $additionalOperators.$indexField.':'.$criterion->value[0].' TO '.$criterion->value[1];
        }

        return $additionalOperators.$indexField.' '.$criterion->operator.' '.$criterion->value[0];
    }
}
