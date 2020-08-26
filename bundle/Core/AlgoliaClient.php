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

final class AlgoliaClient
{
    public const CONFIG = [
        'index_name' => 'Local',
        'app_id' => '3EUIQ3IJ9E',
        'app_secret' => 'ab4bc3a760e3190c2f11203d7089db07'
    ];

    /**
     * @var array
     */
    private $indexes;

    public function getIndex(string $languageCode, ?string $replicaSuffix = null): SearchIndex
    {
        $indexName = self::CONFIG['index_name'].'-'.$languageCode;
        if (null !== $replicaSuffix) {
            $indexName .= '-'.$replicaSuffix;
        }

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