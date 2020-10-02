<?php

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Listener;

use eZ\Publish\SPI\Search\Field;
use eZ\Publish\API\Repository\Values\Content\Field as ContentField;
use eZ\Publish\SPI\Search\FieldType\StringField;
use Novactive\Bundle\eZAlgoliaSearchEngine\Event\ContentTranslationDataFieldConvertEvent;
use eZ\Bundle\EzPublishCoreBundle\Imagine\ImageAsset\AliasGenerator;
use eZ\Publish\API\Repository\ContentService;

class ContentNotSearchableFieldSkipped
{
    /**
     * @var AliasGenerator
     */
    private $imageVariationHandler;

    /**
     * @var ContentService
     */
    private $contentService;

    public function __construct(AliasGenerator $imageVariationHandler, ContentService $contentService)
    {
        $this->imageVariationHandler = $imageVariationHandler;
        $this->contentService = $contentService;
    }

    public function __invoke(ContentTranslationDataFieldConvertEvent $event): void
    {
        $field = $event->getField();
        $fieldDefinition = $event->getFieldDefinition();
        $document = $event->getDocument();
        $content = $event->getContent();

        if ($fieldDefinition->id !== $field->fieldDefinitionId) {
            return;
        }

        if ('ezimage' === $field->type && null !== $field->value->data) {
            $valueContent = $this->contentService->loadContent(
                $content->versionInfo->contentInfo->id,
                [$document->languageCode],
                $content->versionInfo->versionNo
            );

            /* @var ContentField $imageField */
            $imageField = $valueContent->getField($fieldDefinition->identifier);

            $variation = $this->imageVariationHandler->getVariation(
                $imageField,
                $valueContent->versionInfo,
                'medium'
            );

            $document->fields[] = new Field(
                "{$valueContent->getContentType()->identifier}_{$fieldDefinition->identifier}_uri",
                parse_url($variation->uri)['path'],
                new StringField()
            );
        }

        if ('ezimageasset' === $field->type && isset($field->value->data['destinationContentId'])) {
            $valueContent = $this->contentService->loadContent(
                $content->versionInfo->contentInfo->id,
                [$document->languageCode],
                $content->versionInfo->versionNo
            );


            $relationContent = $this->contentService->loadContent($field->value->data['destinationContentId']);

            /* @var ContentField $imageField */
            $imageField = $relationContent->getField('image');

            $variation = $this->imageVariationHandler->getVariation(
                $imageField,
                $relationContent->versionInfo,
                'medium'
            );

            $document->fields[] = new Field(
                "{$valueContent->getContentType()->identifier}_{$fieldDefinition->identifier}_uri",
                parse_url($variation->uri)['path'],
                new StringField()
            );
        }
    }
}
