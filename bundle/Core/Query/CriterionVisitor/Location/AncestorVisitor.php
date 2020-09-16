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
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\Contracts\Ancestor;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\Contracts\AncestorInterface;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\CriterionVisitor;

final class AncestorVisitor implements CriterionVisitor, AncestorInterface
{
    use Ancestor;

    private const INDEX_FIELD = 'location_id_i';

    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\Ancestor;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        return $this->visitAncestor($criterion, self::INDEX_FIELD, $additionalOperators);
    }
}
