<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Event;

use Novactive\Bundle\eZAlgoliaSearchEngine\Mapping\Document;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content;

final class ContentTranslationDataFieldConvertEvent extends DocumentCreateEvent
{
    /**
     * @var Content
     */
    private $content;

    /**
     * @var Field
     */
    private $field;

    /**
     * @var FieldDefinition
     */
    private $fieldDefinition;

    public function __construct(Content $content, Field $field, FieldDefinition $fieldDefinition, Document $document)
    {
        parent::__construct($document);
        $this->content = $content;
        $this->field = $field;
        $this->fieldDefinition = $fieldDefinition;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getField(): Field
    {
        return $this->field;
    }

    public function getFieldDefinition(): FieldDefinition
    {
        return $this->fieldDefinition;
    }
}
