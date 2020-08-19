<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core;

use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\SearchIndex;

class AlgoliaClient
{
    private const CONFIG = [
        'index_name' => 'Local',
        'app_id' => '3EUIQ3IJ9E',
        'app_secret' => 'ab4bc3a760e3190c2f11203d7089db07'
    ];

    private array $indexes;

    public function getIndex(?string $suffix = null): SearchIndex
    {
        $indexName = null === $suffix ? self::CONFIG['index_name'] : self::CONFIG['index_name'].'-'.$suffix;

        if (isset($this->indexes[$indexName])) {
            return $this->indexes[$indexName];
        }

        $client = SearchClient::create(
            self::CONFIG['app_id'],
            self::CONFIG['app_secret']
        );
        $this->indexes[$indexName] = $client->initIndex($indexName);

        return $this->indexes[$indexName];
    }
}