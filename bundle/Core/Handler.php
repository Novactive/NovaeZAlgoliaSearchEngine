<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core;

use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;
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
     * @var LanguageService
     */
    private $languageService;

    /**
     * @var DocumentIdGenerator
     */
    private $documentIdGenerator;

    /**
     * @required
     */
    public function setServices(
        AlgoliaClient $client,
        Converter $converter,
        DocumentSerializer $documentSerializer,
        LanguageService $languageService,
        DocumentIdGenerator $documentIdGenerator
    ): void {
        $this->client = $client;
        $this->converter = $converter;
        $this->documentSerializer = $documentSerializer;
        $this->languageService = $languageService;
        $this->documentIdGenerator = $documentIdGenerator;
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

    public function indexLocation(Location $location): void
    {
        // @todo: should figure out if locations should be indexed as well
//        foreach ($this->converter->convertLocation($location) as $document) {
//            $array = $this->documentSerializer->serialize($document);
//            $array['objectID'] = $document->id;
//            $this->client->getIndex($array['meta_indexed_language_code_s'])->saveObjects([$array]);
//        }

        parent::indexLocation($location);
    }

    public function deleteContent($contentId, $versionId = null): void
    {
        foreach ($this->languageService->loadLanguages() as $language) {
            $this->client->getIndex($language->languageCode)->deleteObject(
                $this->documentIdGenerator->generateContentDocumentId($contentId, $language->languageCode)
            );
        }
    }

    public function deleteLocation($locationId, $versionId = null): void
    {
        foreach ($this->languageService->loadLanguages() as $language) {
            $this->client->getIndex($language->languageCode)->deleteObject(
                $this->documentIdGenerator->generateLocationDocumentId($locationId, $language->languageCode)
            );
        }
    }

    public function purgeIndex(): void
    {
        foreach ($this->languageService->loadLanguages() as $language) {
            $this->client->getIndex($language->languageCode)->clearObjects();
        }
    }
}