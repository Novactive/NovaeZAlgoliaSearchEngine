<?php

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Listener;

use eZ\Publish\SPI\Search\Field;
use eZ\Publish\API\Repository\Values\Content\Field as ContentField;
use eZ\Publish\SPI\Search\FieldType\StringField;
use Novactive\Bundle\eZAlgoliaSearchEngine\Event\ContentNotSearchableFieldSkipEvent;
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

    public function beforeFieldSkipped(ContentNotSearchableFieldSkipEvent $event): void
    {
        $field = $event->getField();
        if ('ezimage' === $field->type) {
            $fieldDefinition = $event->getFieldDefinition();
            $content = $this->contentService->loadContent(
                $event->getContent()->versionInfo->contentInfo->id,
                [$event->getDocument()->languageCode],
                $event->getContent()->versionInfo->versionNo
            );

            /* @var ContentField $imageField */
            $imageField = $content->getField($fieldDefinition->identifier);

            $variation = $this->imageVariationHandler->getVariation(
                $imageField,
                $content->versionInfo,
                'medium'
            );

            $document = $event->getDocument();
            $document->fields[] = new Field(
                "{$content->getContentType()->identifier}_{$fieldDefinition->identifier}_uri",
                $variation->uri,
                new StringField()
            );
        }
    }
}