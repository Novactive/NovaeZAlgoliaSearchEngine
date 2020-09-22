<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Search;

use eZ\Publish\Core\MVC\ConfigResolverInterface;

class SearchQueryFactory
{
    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function create(
        string $term = '',
        string $filter = '',
        array $facets = [],
        int $page = 0,
        int $hitsPerPage = 25
    ): Query {
        $language = $this->configResolver->getParameter('languages')[0];

        return new Query($language, $term, $filter, $facets, $page, $hitsPerPage);
    }
}
