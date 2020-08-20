<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core;

use AppendIterator;
use Doctrine\DBAL\Connection;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Search\Common\IncrementalIndexer;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Search\Handler as SearchHandler;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use Psr\Log\LoggerInterface;
use Exception;

class Indexer extends IncrementalIndexer
{

    /**
     * @var SearchHandler
     */
    protected $searchHandler;

    public function __construct(
        LoggerInterface $logger,
        PersistenceHandler $persistenceHandler,
        Connection $connection,
        SearchHandler $searchHandler
    ) {
        parent::__construct($logger, $persistenceHandler, $connection, $searchHandler);
    }

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
    }
}