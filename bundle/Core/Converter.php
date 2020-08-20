<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core;

use eZ\Publish\Core\Persistence\FieldTypeRegistry;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\Core\Search\Common\FieldRegistry;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Section;
use eZ\Publish\SPI\Persistence\Content\Type as ContentType;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Search\Document;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use eZ\Publish\SPI\Search\FieldType\BooleanField;
use eZ\Publish\SPI\Search\FieldType\DateField;
use eZ\Publish\SPI\Search\FieldType\IntegerField;
use eZ\Publish\SPI\Search\FieldType\MultipleIdentifierField;
use eZ\Publish\SPI\Search\FieldType\MultipleIntegerField;
use eZ\Publish\SPI\Search\FieldType\MultipleStringField;
use eZ\Publish\SPI\Search\FieldType\StringField;
use Iterator;
use Novactive\Bundle\eZAlgoliaSearchEngine\Event\ContentIndexCreateEvent;
use Novactive\Bundle\eZAlgoliaSearchEngine\Event\LocationIndexCreateEvent;
use Novactive\Bundle\eZAlgoliaSearchEngine\Mapping\ContentDocument;
use eZ\Publish\SPI\Search\FieldType\IdentifierField;
use eZ\Publish\Core\Persistence\FieldType as PersistenceFieldType;
use Novactive\Bundle\eZAlgoliaSearchEngine\Mapping\LocationDocument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Converter
{
    private PersistenceHandler $persistenceHandler;

    private FieldRegistry $fieldRegistry;

    private FieldTypeRegistry $fieldTypeRegistry;

    private FieldNameGenerator $fieldNameGenerator;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        PersistenceHandler $persistenceHandler,
        FieldRegistry $fieldRegistry,
        FieldNameGenerator $fieldNameGenerator,
        FieldTypeRegistry $fieldTypeRegistry,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->persistenceHandler = $persistenceHandler;
        $this->fieldRegistry = $fieldRegistry;
        $this->fieldNameGenerator = $fieldNameGenerator;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function convertContent(Content $content): Iterator
    {
        $versionInfo = $content->versionInfo;
        $contentInfo = $content->versionInfo->contentInfo;

        $baseDocument = new ContentDocument();
        $baseDocument->fields[] = new Field(
            'content_id',
            $contentInfo->id,
            new IntegerField()
        );

        $this->addContentInfoFields($baseDocument, $contentInfo);
        $this->addVersionInfoFields($baseDocument, $versionInfo);
        $this->addContentLocationFields($baseDocument, $content);

        foreach ($versionInfo->languageCodes as $languageCode) {
            $isMainTranslation = $contentInfo->mainLanguageCode === $languageCode;

            $document = clone $baseDocument;
            $document->id = sprintf('content-%d-%s', $contentInfo->id, $languageCode);
            $document->contentTypeId = $contentInfo->contentTypeId;
            $document->languageCode = $languageCode;
            $document->isMainTranslation = $isMainTranslation;
            $document->alwaysAvailable = $isMainTranslation && $contentInfo->alwaysAvailable;

            $this->addContentTranslationMetaFields($document, $contentInfo, $languageCode);
            $this->addContentTranslationDataFields($document, $content, $languageCode);

            $this->eventDispatcher->dispatch(new ContentIndexCreateEvent($content, $document));

            yield $document;
        }
    }

    public function convertLocation(Location $location, Content $content = null): Iterator
    {
        if ($content === null) {
            $content = $this->persistenceHandler->contentHandler()->load($location->contentId);
        }

        $versionInfo = $content->versionInfo;
        $contentInfo = $content->versionInfo->contentInfo;

        $baseDocument = new LocationDocument();
        $baseDocument->contentTypeId = $contentInfo->contentTypeId;

        $baseDocument->fields[] = new Field(
            'location_id',
            $location->id,
            new IntegerField()
        );

        $baseDocument->fields[] = new Field(
            'content_id',
            $location->contentId,
            new IntegerField()
        );

        $baseDocument->fields[] = new Field(
            'parent_id',
            $location->parentId,
            new IntegerField()
        );

        $baseDocument->fields[] = new Field(
            'location_remote_id',
            $location->remoteId,
            new IdentifierField()
        );

        $baseDocument->fields[] = new Field(
            'path_string',
            $location->pathString,
            new IdentifierField()
        );

        $baseDocument->fields[] = new Field(
            'priority',
            $location->priority,
            new IntegerField(),
        );

        $baseDocument->fields[] = new Field(
            'depth',
            $location->depth,
            new IntegerField()
        );

        $baseDocument->fields[] = new Field(
            'is_main_location',
            $location->id === $contentInfo->mainLocationId,
            new BooleanField(),
        );

        $baseDocument->fields[] = new Field(
            'hidden',
            $location->hidden,
            new BooleanField()
        );

        $baseDocument->fields[] = new Field(
            'invisible',
            $location->invisible,
            new BooleanField()
        );

        $this->addContentInfoFields($baseDocument, $contentInfo);
        $this->addVersionInfoFields($baseDocument, $versionInfo);

        foreach ($versionInfo->languageCodes as $languageCode) {
            $isMainTranslation = $contentInfo->mainLanguageCode === $languageCode;

            $document = clone $baseDocument;
            $document->id = sprintf('location-%d-%s', $location->id, $languageCode);
            $document->contentTypeId = $contentInfo->contentTypeId;
            $document->languageCode = $languageCode;
            $document->isMainTranslation = $isMainTranslation;
            $document->alwaysAvailable = $isMainTranslation && $contentInfo->alwaysAvailable;

            $this->addContentTranslationMetaFields($document, $contentInfo, $languageCode);
            $this->addContentTranslationDataFields($document, $content, $languageCode);

            $this->eventDispatcher->dispatch(new LocationIndexCreateEvent($location, $document));

            yield $document;
        }
    }

    private function addContentInfoFields(Document $document, ContentInfo $contentInfo): void
    {
        $section = $this->persistenceHandler->sectionHandler()->load($contentInfo->sectionId);
        $contentType = $this->persistenceHandler->contentTypeHandler()->load($contentInfo->contentTypeId);

        $document->fields[] = new Field(
            'content_remote_id',
            $contentInfo->remoteId,
            new IdentifierField()
        );

        $document->fields[] = new Field(
            'content_name',
            $contentInfo->name,
            new StringField()
        );

        $this->addContentTypeFields($document, $contentType);
        $this->addUserMetadataFields($document, $contentInfo);

        $this->addLanguagesFields($document, $contentInfo);
        $this->addSectionFields($document, $section);
        $this->addDateMetadataFields($document, $contentInfo);
        $this->addObjectStateFields($document, $contentInfo);
    }

    private function addVersionInfoFields(Document $document, VersionInfo $versionInfo): void
    {
        $document->fields[] = new Field(
            'content_language_codes',
            array_keys($versionInfo->names),
            new MultipleStringField()
        );

        $document->fields[] = new Field(
            'content_version_creator_user_id',
            $versionInfo->creatorId,
            new IntegerField()
        );
    }

    private function addContentLocationFields(Document $document, Content $content): void
    {
        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent(
            $content->versionInfo->contentInfo->id
        );

        $mainLocation = null;
        $isSomeLocationVisible = false;
        $locationData = [];

        foreach ($locations as $location) {
            $locationData['ids'][] = $location->id;
            $locationData['parent_ids'][] = $location->parentId;
            $locationData['remote_ids'][] = $location->remoteId;
            $locationData['path_strings'][] = $location->pathString;

            if ($location->id === $content->versionInfo->contentInfo->mainLocationId) {
                $mainLocation = $location;
            }

            if (!$location->hidden && !$location->invisible) {
                $isSomeLocationVisible = true;
            }
        }

        if (!empty($locationData)) {
            $document->fields[] = new Field(
                'location_id',
                $locationData['ids'],
                new MultipleIntegerField()
            );

            $document->fields[] = new Field(
                'location_parent_id',
                $locationData['parent_ids'],
                new MultipleIntegerField()
            );

            $document->fields[] = new Field(
                'location_remote_id',
                $locationData['remote_ids'],
                new MultipleIdentifierField()
            );

            $document->fields[] = new Field(
                'location_path_string',
                $locationData['path_strings'],
                new MultipleIdentifierField()
            );
        }

        if ($mainLocation !== null) {
            $document->fields[] = new Field(
                'main_location',
                $mainLocation->id,
                new IntegerField()
            );

            $document->fields[] = new Field(
                'main_location_parent',
                $mainLocation->parentId,
                new IntegerField()
            );

            $document->fields[] = new Field(
                'main_location_remote_id',
                $mainLocation->remoteId,
                new IdentifierField()
            );

            $document->fields[] = new Field(
                'main_location_visible',
                !$mainLocation->hidden && !$mainLocation->invisible,
                new BooleanField()
            );

            $document->fields[] = new Field(
                'main_location_path',
                $mainLocation->pathString,
                new IdentifierField()
            );

            $document->fields[] = new Field(
                'main_location_depth',
                $mainLocation->depth,
                new IntegerField()
            );

            $document->fields[] = new Field(
                'main_location_priority',
                $mainLocation->priority,
                new IntegerField()
            );
        }

        $document->fields[] = new Field(
            'location_visible',
            $isSomeLocationVisible,
            new BooleanField()
        );
    }

    private function addContentTranslationMetaFields(
        Document $document,
        ContentInfo $contentInfo,
        string $languageCode
    ): void {
        $isMainTranslation = $languageCode === $contentInfo->mainLanguageCode;

        $document->fields[] = new Field(
            'meta_indexed_language_code',
            $languageCode,
            new StringField()
        );

        $document->fields[] = new Field(
            'meta_indexed_is_main_translation',
            $isMainTranslation,
            new BooleanField()
        );

        $document->fields[] = new Field(
            'meta_indexed_is_main_translation_and_always_available',
            $isMainTranslation && $contentInfo->alwaysAvailable,
            new BooleanField()
        );
    }

    private function addContentTranslationDataFields(Document $document, Content $content, string $languageCode): void
    {
        $contentType = $this->persistenceHandler->contentTypeHandler()->load(
            $content->versionInfo->contentInfo->contentTypeId
        );

        foreach ($content->fields as $field) {
            if ($field->languageCode !== $languageCode) {
                continue;
            }

            foreach ($contentType->fieldDefinitions as $fieldDefinition) {
                if ($fieldDefinition->id !== $field->fieldDefinitionId || !$fieldDefinition->isSearchable) {
                    continue;
                }

                /** @var PersistenceFieldType $fieldType */
                $fieldType = $this->fieldTypeRegistry->getFieldType($fieldDefinition->fieldType);

                $document->fields[] = new Field(
                    $this->fieldNameGenerator->getName('is_empty', $fieldDefinition->identifier),
                    $fieldType->isEmptyValue($field->value),
                    new BooleanField()
                );

                $indexFields = $this->fieldRegistry
                    ->getType($field->type)
                    ->getIndexData($field, $fieldDefinition);

                foreach ($indexFields as $indexField) {
                    if ($indexField->value === null) {
                        continue;
                    }

                    $document->fields[] = new Field(
                        $this->fieldNameGenerator->getName(
                            $indexField->name,
                            $fieldDefinition->identifier,
                            $contentType->identifier
                        ),
                        $indexField->value,
                        $indexField->type
                    );
                }
            }
        }
    }

    private function addContentTypeFields(Document $document, ContentType $contentType): void
    {
        $document->fields[] = new Field(
            'content_type_id',
            $contentType->id,
            new IntegerField()
        );

        $document->fields[] = new Field(
            'content_type_group_id',
            $contentType->groupIds,
            new MultipleIntegerField()
        );
    }

    private function addUserMetadataFields(Document $document, ContentInfo $contentInfo): void
    {
        $document->fields[] = new Field(
            'content_owner_user_id',
            $contentInfo->ownerId,
            new IntegerField()
        );

        $document->fields[] = new Field(
            'content_owner_user_group_id',
            $this->getContentOwnerUserGroupIds($contentInfo),
            new MultipleIntegerField()
        );
    }

    private function getContentOwnerUserGroupIds(ContentInfo $contentInfo): array
    {
        $locationHandler = $this->persistenceHandler->locationHandler();

        $locationIds = [];
        foreach ($locationHandler->loadLocationsByContent($contentInfo->ownerId) as $location) {
            $path = explode('/', trim($location->pathString, '/'));
            // Remove Location of Content with $contentId
            array_pop($path);
            // Remove Root Location id
            array_shift($path);

            // was array_merge before
            $locationIds = [...$locationIds, ...$path];
        }

        $contentIds = [];
        if (!empty($locationIds)) {
            $locationIds = array_unique($locationIds);
            foreach ($locationHandler->loadList($locationIds) as $location) {
                $contentIds[] = $location->contentId;
            }
        }

        return array_unique($contentIds);
    }

    private function addLanguagesFields(Document $document, ContentInfo $contentInfo): void
    {
        $document->fields[] = new Field(
            'content_main_language_code',
            $contentInfo->mainLanguageCode,
            new IdentifierField(),
        );

        $document->fields[] = new Field(
            'content_always_available',
            $contentInfo->alwaysAvailable,
            new BooleanField(),
        );
    }

    private function addSectionFields(Document $document, Section $section): void
    {
        $document->fields[] = new Field(
            'section_id',
            $section->id,
            new IntegerField()
        );

        $document->fields[] = new Field(
            'section_identifier',
            $section->identifier,
            new IdentifierField()
        );

        $document->fields[] = new Field(
            'section_name',
            $section->name,
            new StringField()
        );
    }

    private function addDateMetadataFields(Document $document, ContentInfo $contentInfo): void
    {
        $document->fields[] = new Field(
            'content_modification_date',
            $contentInfo->modificationDate,
            new DateField()
        );

        $document->fields[] = new Field(
            'content_publication_date',
            $contentInfo->publicationDate,
            new DateField()
        );
    }

    private function addObjectStateFields(Document $document, ContentInfo $contentInfo): void
    {
        $document->fields[] = new Field(
            'object_state_id',
            $this->getObjectStateIds($contentInfo->id),
            new MultipleIntegerField()
        );
    }

    private function getObjectStateIds(int $contentId): array
    {
        $objectStateIds = [];

        $objectStateHandler = $this->persistenceHandler->objectStateHandler();
        foreach ($objectStateHandler->loadAllGroups() as $objectStateGroup) {
            $objectStateIds[] = $objectStateHandler->getContentState(
                $contentId,
                $objectStateGroup->id
            )->id;
        }

        return $objectStateIds;
    }
}