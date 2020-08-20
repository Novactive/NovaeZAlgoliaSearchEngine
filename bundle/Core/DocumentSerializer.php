<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core;

use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\Core\Search\Common\FieldValueMapper;
use eZ\Publish\SPI\Search\Document;

class DocumentSerializer
{

    private FieldValueMapper $fieldValueMapper;

    private FieldNameGenerator $nameGenerator;

    public function __construct(FieldValueMapper $fieldValueMapper, FieldNameGenerator $fieldNameGenerator)
    {
        $this->fieldValueMapper = $fieldValueMapper;
        $this->nameGenerator = $fieldNameGenerator;
    }

    public function serialize(Document $document): array
    {
        $body = [];
        foreach ($document->fields as $field) {
            $fieldName = $this->nameGenerator->getTypedName($field->name, $field->type);
            if ($this->fieldValueMapper->canMap($field)) {
                $fieldValue = $this->fieldValueMapper->map($field);
            } else {
                $fieldValue = $field->value;
            }

            $body[$fieldName] = $fieldValue;
        }

        return $body;
    }
}