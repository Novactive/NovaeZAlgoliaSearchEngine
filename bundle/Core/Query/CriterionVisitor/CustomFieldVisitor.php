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
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\Contracts\Helper;

final class CustomFieldVisitor implements CriterionVisitor
{
    use Helper;

    public function supports(Criterion $criterion): bool
    {
        return
            $criterion instanceof Criterion\CustomField &&
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
            $queries = [];
            $values = (array) $criterion->value;

            foreach ($values as $value) {
                $preparedValue = $this->escapeQuote($this->toString($value), true);

                if (preg_match('#^/.*/$#', $preparedValue)) {
                    $queries[] = $additionalOperators.$criterion->target.':'.$preparedValue;
                } else {
                    $expression = (is_numeric($preparedValue) ? '='.$preparedValue : ':"'.$preparedValue.'"');
                    $queries[] = $additionalOperators.$criterion->target.$expression;
                }
            }

            return '('.implode('NOT ' === $additionalOperators ? ' AND ' : ' OR ', $queries).')';
        }

        if ($criterion->operator === Criterion\Operator::BETWEEN) {
            return $additionalOperators.$criterion->target.':'.
                   $this->escapeQuote($this->toString($criterion->value[0]), true).' TO '.
                   $this->escapeQuote($this->toString($criterion->value[1]), true);
        }

        return $additionalOperators.$criterion->target.' '.$criterion->operator.' '.$criterion->value;
    }
}
