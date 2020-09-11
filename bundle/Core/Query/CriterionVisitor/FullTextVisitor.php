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
use RuntimeException;

final class FullTextVisitor implements CriterionVisitor
{
    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\FullText;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        $message = 'FullText criterion is not implemented yet. ';
        $message .= 'Check out the Algolia reference for the possible ways to implement it: ';
        $message .= 'https://www.algolia.com/doc/api-reference/api-parameters/query/';
        throw new RuntimeException($message);
    }
}
