<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\ResultExtractor\FacetResultExtractor;

abstract class AbstractTermsResultExtractor implements FacetResultExtractor
{
    protected function extractEntries(array $data): array
    {
        $entries = [];
        foreach ($data as $facet) {
            foreach ($facet as $key => $value) {
                $entries[$key] = $value;
            }
        }

        return $entries;
    }
}
