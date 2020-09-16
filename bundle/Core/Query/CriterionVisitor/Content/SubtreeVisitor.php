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
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\CriterionVisitor;
use eZ\Publish\Core\Repository\Values\Content\Query\Criterion\PermissionSubtree;
use RuntimeException;

final class SubtreeVisitor implements CriterionVisitor
{
    public function supports(Criterion $criterion): bool
    {
        return ($criterion instanceof Criterion\Subtree || $criterion instanceof PermissionSubtree) &&
               ($criterion->operator === Criterion\Operator::EQ || $criterion->operator === Criterion\Operator::IN);
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        $message = 'Subtree criterion is not implemented yet. ';
        $message .= 'Check out the Algolia reference for the possible ways to implement it: ';
        $message .= 'https://www.algolia.com/doc/api-reference/api-parameters/query/';
        throw new RuntimeException($message);
    }
}
