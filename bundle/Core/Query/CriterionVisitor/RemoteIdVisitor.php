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
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\Contracts\CommonVisitor;

final class RemoteIdVisitor implements CriterionVisitor
{
    use CommonVisitor;

    private const INDEX_FIELD = 'content_remote_id_id';

    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\RemoteId;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        return $this->visitValues($criterion->value, self::INDEX_FIELD . ':"%s"', $additionalOperators);
    }
}
