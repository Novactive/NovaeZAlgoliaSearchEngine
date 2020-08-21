<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\Core\Search\Legacy\Content\Handler as LegacyHandler;

class Handler extends LegacyHandler
{
    /**
     * @var AlgoliaClient
     */
    private $client;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var DocumentSerializer
     */
    private $documentSerializer;

    /**
     * @required
     */
    public function setServices(
        AlgoliaClient $client,
        Converter $converter,
        DocumentSerializer $documentSerializer
    ): void {
        $this->client = $client;
        $this->converter = $converter;
        $this->documentSerializer = $documentSerializer;
    }

    public function indexContent(Content $content): void
    {
        foreach ($this->converter->convertContent($content) as $document) {
            $array = $this->documentSerializer->serialize($document);
            $array['objectID'] = $document->id;
            $this->client->getIndex($array['meta_indexed_language_code_s'])->saveObjects([$array]);
        }

        parent::indexContent($content);
    }
}