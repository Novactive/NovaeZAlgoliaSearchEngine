<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringSortClause;

class Replica extends SortClause implements FilteringSortClause
{
    public function __construct(string $replicaKey)
    {
        parent::__construct($replicaKey, Query::SORT_ASC);
    }
}