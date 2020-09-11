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
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\Core\Search\Legacy\Content\Handler as LegacyHandler;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\Search;

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
     * @var Search
     */
    private $contentSearchService;

    /**
     * @var Search
     */
    private $locationSearchService;

    /**
     * @required
     */
    public function setServices(
        AlgoliaClient $client,
        Converter $converter,
        DocumentSerializer $documentSerializer,
        LanguageService $languageService,
        DocumentIdGenerator $documentIdGenerator,
        Search $contentSearch,
        Search $locationSearch
    ): void {
        $this->client = $client;
        $this->converter = $converter;
        $this->documentSerializer = $documentSerializer;
        $this->languageService = $languageService;
        $this->documentIdGenerator = $documentIdGenerator;
        $this->contentSearchService = $contentSearch;
        $this->locationSearchService = $locationSearch;
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
        foreach ($this->converter->convertLocation($location) as $document) {
            $array = $this->documentSerializer->serialize($document);
            $array['objectID'] = $document->id;
            $this->client->getIndex($array['meta_indexed_language_code_s'])->saveObjects([$array]);
        }

        parent::indexLocation($location);
    }

    public function deleteContent($contentId, $versionId = null): void
    {
        foreach ($this->languageService->loadLanguages() as $language) {
            $this->client->getIndex($language->languageCode)->deleteObject(
                $this->documentIdGenerator->generateContentDocumentId($contentId, $language->languageCode)
            );
        }

        parent::deleteContent($contentId, $versionId);
    }

    public function deleteLocation($locationId, $versionId = null): void
    {
        foreach ($this->languageService->loadLanguages() as $language) {
            $this->client->getIndex($language->languageCode)->deleteObject(
                $this->documentIdGenerator->generateLocationDocumentId($locationId, $language->languageCode)
            );
        }

        parent::deleteLocation($locationId, $versionId);
    }

    public function purgeIndex(): void
    {
        foreach ($this->languageService->loadLanguages() as $language) {
            $this->client->getIndex($language->languageCode)->clearObjects();
        }

        parent::purgeIndex();
    }

    public function findSingle(Criterion $filter, array $languageFilter = []): ContentInfo
    {
        $query = new Query();
        $query->filter = $filter;
        $query->query = new Criterion\MatchAll();
        $query->offset = 0;
        $query->limit = 1;

        $result = $this->contentSearchService->execute($query, 'content', $languageFilter);

        if ($result->totalCount < 1) {
            throw new NotFoundException('Content', 'findSingle() found no content for the given $filter');
        }

        if ($result->totalCount > 1) {
            throw new InvalidArgumentException('totalCount', 'findSingle() found more then one Content item for the given $filter');
        }

        return reset($result->searchHits)->valueObject;
    }

    public function findContent(Query $query, array $languageFilter = []): SearchResult
    {
        return $this->contentSearchService->execute($query, 'content', $languageFilter);

        //@todo: should be replaced eventually
        //return parent::findContent($query, $languageFilter);
    }

    public function findLocations(LocationQuery $query, array $languageFilter = []): SearchResult
    {
        if (!isset($languageFilter['languages'])) {
            $languageFilter['languages'] = ['eng-GB'];
        }

        return $this->locationSearchService->execute($query, 'location', $languageFilter);

        //@todo: should be replaced eventually
        //return parent::findLocations($query, $languageFilter);
    }
}