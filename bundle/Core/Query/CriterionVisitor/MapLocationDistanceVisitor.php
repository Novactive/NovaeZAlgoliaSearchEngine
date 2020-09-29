<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

final class MapLocationDistanceVisitor implements CriterionVisitor
{
    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\MapLocationDistance;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        $message = 'MapLocationDistance criterion is not implemented. ';
        $message .= 'Check out the Algolia reference for the possible ways to implement it: ';
        $message .= 'https://www.algolia.com/doc/guides/managing-results/refine-results/';
        $message .= 'geolocation/how-to/filter-results-around-a-location/';
        throw new NotImplementedException($message);
    }
}
