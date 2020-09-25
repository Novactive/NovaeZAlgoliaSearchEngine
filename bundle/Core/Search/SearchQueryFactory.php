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
use Novactive\Bundle\eZAlgoliaSearchEngine\Event\QueryCreateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SearchQueryFactory
{
    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(ConfigResolverInterface $configResolver, EventDispatcherInterface $eventDispatcher)
    {
        $this->configResolver = $configResolver;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(
        string $term = '',
        string $filter = '',
        array $facets = [],
        int $page = 0,
        int $hitsPerPage = 25
    ): Query {
        $language = $this->configResolver->getParameter('languages')[0];

        $query = new Query($language, $term, $filter, $facets, $page, $hitsPerPage);

        $this->eventDispatcher->dispatch(new QueryCreateEvent($query));

        return $query;
    }
}
