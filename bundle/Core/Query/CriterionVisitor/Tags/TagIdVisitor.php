<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\Tags;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId;

final class TagIdVisitor extends TagsVisitor
{
    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof TagId;
    }
}