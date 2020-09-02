<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\Content;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\CriterionVisitor;

final class VisibilityVisitor implements CriterionVisitor
{
    private const INDEX_FIELD = 'location_visible_b';

    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\Visibility && $criterion->operator === Criterion\Operator::EQ;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        return $additionalOperators.self::INDEX_FIELD.':'.
               ($criterion->value[0] === Criterion\Visibility::VISIBLE ? 'true' : 'false');
    }
}
