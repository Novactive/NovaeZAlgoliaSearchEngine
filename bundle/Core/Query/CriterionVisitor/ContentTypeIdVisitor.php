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

final class ContentTypeIdVisitor implements CriterionVisitor
{
    private const INDEX_FIELD = 'content_type_id_i';

    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\ContentTypeId;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        return '('.implode(
                'NOT ' === $additionalOperators ? ' AND ' : ' OR ',
                array_map(
                    static function ($value) use ($additionalOperators) {
                        return $additionalOperators.self::INDEX_FIELD.'='.$value;
                    },
                    $criterion->value
                )
            ).')';
    }
}
