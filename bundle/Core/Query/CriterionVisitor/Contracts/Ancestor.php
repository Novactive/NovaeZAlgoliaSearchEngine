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

trait Ancestor
{
    public function visitAncestor(Criterion $criterion, string $indexField, string $additionalOperators): string
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
                    static function ($value) use ($additionalOperators, $indexField) {
                        return $additionalOperators.$indexField.'='.$value;
                    },
                    array_keys($idSet)
                )
            ).
            ')';
    }
}