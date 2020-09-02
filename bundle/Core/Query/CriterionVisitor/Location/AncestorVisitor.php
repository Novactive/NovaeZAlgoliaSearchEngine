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

final class AncestorVisitor implements CriterionVisitor
{
    private const INDEX_FIELD = 'location_id_i';

    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\Ancestor;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        $idSet = array();
        foreach ($criterion->value as $value) {
            foreach (explode('/', trim($value, '/')) as $id) {
                $idSet[$id] = true;
            }
        }

        return '('.
               implode(
                   'NOT ' === $additionalOperators ? ' AND ' : ' OR ',
                   array_map(
                       static function ($value) use ($additionalOperators) {
                           return $additionalOperators.self::INDEX_FIELD.'='.$value;
                       },
                       array_keys($idSet)
                   )
               ).
               ')';
    }
}
