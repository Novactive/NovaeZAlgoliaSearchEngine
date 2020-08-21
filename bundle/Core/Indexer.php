<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core;

use eZ\Publish\Core\Search\Common\IncrementalIndexer;
use eZ\Publish\SPI\Search\Handler as SearchHandler;

class Indexer extends IncrementalIndexer
{
    /**
     * @var SearchHandler
     */
    protected $searchHandler;

    public function getName(): string
    {
        return 'eZ Platform Algolia Search Engine';
    }

    public function purge(): void
    {
        $this->searchHandler->purgeIndex();
    }

    public function updateSearchIndex(array $contentIds, $commit): void
    {
        //@todo: need to reproduce what is done on this method in Elastic Search
    }
}