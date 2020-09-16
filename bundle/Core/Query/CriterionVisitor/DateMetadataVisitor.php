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
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\Contracts\CommonVisitor;
use RuntimeException;

final class DateMetadataVisitor implements CriterionVisitor
{
    use CommonVisitor;

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
        return $this->visitWithOperators($criterion, $additionalOperators, $this->getTargetField($criterion));
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
