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

final class IsMainLocationVisitor implements CriterionVisitor
{
    private const INDEX_FIELD = 'is_main_location_b';

    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\Location\IsMainLocation;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        return $additionalOperators.self::INDEX_FIELD.':'.
               ($criterion->value[0] === Criterion\Location\IsMainLocation::MAIN ? 'true' : 'false');
    }
}
