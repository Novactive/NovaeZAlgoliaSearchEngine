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
    private AlgoliaClient $client;

    private Converter $converter;

    private DocumentSerializer $documentSerializer;

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
        $data = [];
        foreach ($this->converter->convertContent($content) as $document) {
            dump($document);
            $array = $this->documentSerializer->serialize($document);
            $array['objectID'] = $document->id;
            $data[] = $array;
        }
        dd($data);
        $this->client->getIndex()->saveObjects($data);

        parent::indexContent($content);
    }
}