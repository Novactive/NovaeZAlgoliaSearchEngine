<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\ResultExtractor;

use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;

interface ResultExtractor
{
    public function extract(array $data, iterable $facetBuilders): SearchResult;

    public function getExpectedSourceFields(): array;
}
