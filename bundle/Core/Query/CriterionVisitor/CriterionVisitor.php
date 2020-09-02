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

interface CriterionVisitor
{
    public function supports(Criterion $criterion): bool;

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string;
}
