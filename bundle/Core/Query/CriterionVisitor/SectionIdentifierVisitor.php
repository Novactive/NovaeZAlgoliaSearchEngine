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

final class SectionIdentifierVisitor implements CriterionVisitor
{
    private const INDEX_FIELD = 'section_identifier_id';

    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\SectionIdentifier;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        return sprintf(
            '(%s)',
            implode(
                'NOT ' === $additionalOperators ? ' AND ' : ' OR ',
                array_map(
                    static function (string $value) use ($additionalOperators) {
                        return $additionalOperators.self::INDEX_FIELD.':"'.$value.'"';
                    },
                    (array) $criterion->value
                )
            )
        );
    }
}
