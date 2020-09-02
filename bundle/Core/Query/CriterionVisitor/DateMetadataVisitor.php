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

final class DateMetadataVisitor implements CriterionVisitor
{
    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\DateMetadata &&
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
        $fieldName = $this->getTargetField($criterion);

        if (\in_array($criterion->operator, [Criterion\Operator::EQ, Criterion\Operator::IN], true)) {
            $values = array();
            foreach ($criterion->value as $value) {
                $values[] = $additionalOperators.$fieldName.'='.$value;
            }

            return '('.implode('NOT ' === $additionalOperators ? ' AND ' : ' OR ', $values).')';
        }

        if ($criterion->operator === Criterion\Operator::BETWEEN) {
            if (2 !== count($criterion->value)) {
                throw new RuntimeException("Unsupported number of values for {$criterion->operator} operator");
            }

            return $additionalOperators.$fieldName.':'.$criterion->value[0].' TO '.$criterion->value[1];
        }

        return $additionalOperators.$fieldName.' '.$criterion->operator.' '.$criterion->value[0];
    }

    private function getTargetField(Criterion $criterion): string
    {
        switch ($criterion->target) {
            case Criterion\DateMetadata::CREATED:
                return 'content_publication_date_timestamp_i';
            case Criterion\DateMetadata::MODIFIED:
                return 'content_modification_date_timestamp_i';
            default:
                throw new RuntimeException("Unsupported DateMetadata criterion target {$criterion->value}");
        }
    }
}
