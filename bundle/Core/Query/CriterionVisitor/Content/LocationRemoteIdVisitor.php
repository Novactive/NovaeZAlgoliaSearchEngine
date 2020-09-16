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
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\Contracts\CommonVisitor;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\CriterionVisitor;

final class LocationRemoteIdVisitor implements CriterionVisitor
{
    use CommonVisitor;

    private const INDEX_FIELD = 'location_remote_id_mid';

    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\LocationRemoteId;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        return $this->visitValues($criterion->value, self::INDEX_FIELD . ':"%s"', $additionalOperators);
    }
}
