<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core;

use Doctrine\DBAL\Connection;
use Exception;
use eZ\Publish\Core\Search\Common\IncrementalIndexer;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Search\Handler as SearchHandler;
use Psr\Log\LoggerInterface;
use Iterator;

class Indexer extends IncrementalIndexer
{
    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var Converter;
     */
    private $converter;

    /**
     * @var DocumentSerializer
     */
    private $documentSerializer;

    public function __construct(
        LoggerInterface $logger,
        PersistenceHandler $persistenceHandler,
        Connection $connection,
        SearchHandler $searchHandler,
        Handler $handler,
        Converter $converter,
        DocumentSerializer $documentSerializer
    ) {
        parent::__construct($logger, $persistenceHandler, $connection, $searchHandler);
        $this->handler = $handler;
        $this->converter = $converter;
        $this->documentSerializer = $documentSerializer;
    }

    public function getName(): string
    {
        return 'eZ Platform Algolia Search Engine';
    }

    public function purge(): void
    {
        $this->handler->purgeIndex();
    }

    public function updateSearchIndex(array $contentIds, $commit): void
    {
        $contentHandler = $this->persistenceHandler->contentHandler();
        $locationHandler = $this->persistenceHandler->locationHandler();

        $langObjectSet = [];
        foreach ($contentIds as $contentId) {
            try {
                $contentInfo = $contentHandler->loadContentInfo($contentId);
                if ($contentInfo->status === ContentInfo::STATUS_PUBLISHED) {
                    $content = $contentHandler->load($contentId);
                    $this->convertDocuments($this->converter->convertContent($content), $langObjectSet);

                    foreach ($locationHandler->loadLocationsByContent($contentId) as $location) {
                        $this->convertDocuments($this->converter->convertLocation($location), $langObjectSet);
                    }
                } else {
                    $this->handler->deleteContent($contentId);
                }
            } catch (Exception $e) {
                $this->handler->deleteContent($contentId);
                $this->logger->error(
                    'Unable to index the content',
                    [
                        'contentId' => $contentId,
                        'error' => $e->getMessage(),
                    ]
                );
            }
        }
        foreach ($langObjectSet as $languageCode => $objects) {
            $this->handler->reindex($languageCode, $objects);
        }
    }

    private function convertDocuments(iterator $documents, &$langObjectSet): void
    {
        foreach ($documents as $document) {
            $array = $this->documentSerializer->serialize($document);
            $array['objectID'] = $document->id;
            $langObjectSet[$array['meta_indexed_language_code_s']][] = $array;
        }
    }
}