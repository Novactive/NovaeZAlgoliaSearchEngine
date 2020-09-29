<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core;

use Exception;
use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\Core\Search\Legacy\Content\Handler as LegacyHandler;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\Search;
use Psr\Log\LoggerInterface;
use Novactive\Bundle\eZAlgoliaSearchEngine\Mapping\ParametersResolver;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ParametersResolver
     */
    private $parametersResolver;

    /**
     * @var ContentService
     */
    private $contentService;

    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

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
        Search $locationSearch,
        LoggerInterface $logger,
        ParametersResolver $parametersResolver,
        ContentService $contentService,
        ContentTypeService $contentTypeService
    ): void {
        $this->client = $client;
        $this->converter = $converter;
        $this->documentSerializer = $documentSerializer;
        $this->languageService = $languageService;
        $this->documentIdGenerator = $documentIdGenerator;
        $this->contentSearchService = $contentSearch;
        $this->locationSearchService = $locationSearch;
        $this->logger = $logger;
        $this->parametersResolver = $parametersResolver;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
    }

    public function indexContent(Content $content): void
    {
        $contentType = $this->contentTypeService->loadContentType($content->versionInfo->contentInfo->contentTypeId);
        if ($this->parametersResolver->ifContentTypeAllowed($contentType->identifier)) {
            try {
                $contentLanguages = $mainTranslation = [];
                foreach ($this->converter->convertContent($content) as $document) {
                    $serialized = $this->documentSerializer->serialize($document);
                    $serialized['objectID'] = $document->id;
                    $this->reindex($serialized['meta_indexed_language_code_s'], [$serialized]);
                    $contentLanguages[] = $serialized['meta_indexed_language_code_s'];
                    if ($document->isMainTranslation) {
                        $mainTranslation = $serialized;
                    }
                }

                foreach ($this->languageService->loadLanguages() as $language) {
                    if (!\in_array($language->languageCode, $contentLanguages, true)) {
                        $this->reindex($language->languageCode, [$mainTranslation]);
                    }
                }
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        parent::indexContent($content);
    }

    public function indexLocation(Location $location): void
    {
        $content = $this->contentService->loadContent($location->contentId);
        if ($this->parametersResolver->ifContentTypeAllowed($content->getContentType()->identifier)) {
            try {
                $locationLanguages = $mainTranslation = [];
                foreach ($this->converter->convertLocation($location) as $document) {
                    $serialized = $this->documentSerializer->serialize($document);
                    $serialized['objectID'] = $document->id;
                    $this->reindex($serialized['meta_indexed_language_code_s'], [$serialized]);
                    $locationLanguages[] = $serialized['meta_indexed_language_code_s'];
                    if ($document->isMainTranslation) {
                        $mainTranslation = $serialized;
                    }
                }

                foreach ($this->languageService->loadLanguages() as $language) {
                    if (!\in_array($language->languageCode, $locationLanguages, true)) {
                        $this->reindex($language->languageCode, [$mainTranslation]);
                    }
                }
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        parent::indexLocation($location);
    }

    public function reindex(string $languageCode, array $objects): void
    {
        $this->client->getIndex($languageCode)->saveObjects($objects);
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

    public function findSingle(Criterion $filter, array $languageFilter = []): ValueObject
    {
        $query = new Query();
        $query->filter = $filter;
        $query->query = new Criterion\MatchAll();
        $query->offset = 0;
        $query->limit = 1;

        try {
            $result = $this->contentSearchService->execute($query, 'content', $languageFilter);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return parent::findSingle($filter, $languageFilter);
        }

        if ($result->totalCount < 1) {
            throw new NotFoundException('Content', 'findSingle() found no content for the given $filter');
        }

        if ($result->totalCount > 1) {
            throw new InvalidArgumentException(
                'totalCount',
                'findSingle() found more then one Content item for the given $filter'
            );
        }

        return reset($result->searchHits)->valueObject;
    }

    public function findContent(Query $query, array $languageFilter = []): SearchResult
    {
        try {
            return $this->contentSearchService->execute($query, 'content', $languageFilter);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return parent::findContent($query, $languageFilter);
        }
    }

    public function findLocations(LocationQuery $query, array $languageFilter = []): SearchResult
    {
        if (!isset($languageFilter['languages'])) {
            $languageFilter['languages'] = ['eng-GB'];
        }

        try {
            return $this->locationSearchService->execute($query, 'location', $languageFilter);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return parent::findLocations($query, $languageFilter);
        }
    }
}
