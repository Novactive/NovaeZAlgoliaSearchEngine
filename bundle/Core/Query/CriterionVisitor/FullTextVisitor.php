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

final class FullTextVisitor implements CriterionVisitor
{
    public const placeholder = 'fulltextRequest={%s}';

    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\FullText;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        return sprintf(self::placeholder, $criterion->value);
    }
}
