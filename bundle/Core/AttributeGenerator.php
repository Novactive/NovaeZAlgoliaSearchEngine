<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Persistence\FieldTypeRegistry;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\Core\Search\Common\FieldRegistry;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Search\FieldType\BooleanField;
use eZ\Publish\SPI\Search\FieldType\FullTextField;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use Novactive\Bundle\eZAlgoliaSearchEngine\DependencyInjection\Configuration;

final class AttributeGenerator
{
    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    /**
     * @var FieldNameGenerator
     */
    private $fieldNameGenerator;

    /**
     * @var FieldRegistry
     */
    private $fieldRegistry;

    /**
     * @var FieldTypeRegistry
     */
    private $fieldTypeRegistry;

    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    public function __construct(
        ContentTypeService $contentTypeService,
        FieldNameGenerator $fieldNameGenerator,
        FieldRegistry $fieldRegistry,
        FieldTypeRegistry $fieldTypeRegistry,
        ConfigResolverInterface $configResolver
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->fieldNameGenerator = $fieldNameGenerator;
        $this->fieldRegistry = $fieldRegistry;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        $this->configResolver = $configResolver;
    }

    public function getCustomSearchableAttributes(bool $onlyFullText = false): array
    {
        $data = [];
        $excludeContentTypes = $this->configResolver->getParameter('exclude_content_types', Configuration::NAMESPACE);
        $includeContentTypes = $this->configResolver->getParameter('include_content_types', Configuration::NAMESPACE);

        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            foreach ($this->contentTypeService->loadContentTypes($contentTypeGroup) as $contentType) {
                if (count($includeContentTypes) > 0 &&
                    !\in_array($contentType->identifier, $includeContentTypes, true)) {
                    continue;
                }
                if (count($excludeContentTypes) > 0 &&
                    \in_array($contentType->identifier, $excludeContentTypes, true)) {
                    continue;
                }

                /* @var FieldDefinition $fieldDefinition */
                foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
                    if ($fieldDefinition->isSearchable) {

                        $fieldType = $this->fieldTypeRegistry->getFieldType($fieldDefinition->fieldTypeIdentifier);
                        $emptyValue = $fieldType->getEmptyValue();
                        $fakeValue = new FieldValue(
                            [
                                'data' => $emptyValue->data
                            ]
                        );
                        $fakeField = new Field(
                            [
                                'value' => $fakeValue,
                                'fieldDefinitionId' => $fieldDefinition->id,
                                'type' => $fieldDefinition->fieldTypeIdentifier
                            ]
                        );
                        $fakeFieldDefinition = new PersistenceFieldDefinition(
                            [
                                'identifier' => $fieldDefinition->identifier,
                                'fieldType' => $fieldDefinition->fieldTypeIdentifier
                            ]
                        );

                        $indexFields = $this->fieldRegistry
                            ->getType($fakeField->type)
                            ->getIndexData($fakeField, $fakeFieldDefinition);

                        foreach ($indexFields as $indexField) {
                            if (!$onlyFullText || $indexField->type instanceof FullTextField) {
                                $fullName = $this->fieldNameGenerator->getName(
                                    $indexField->name,
                                    $fieldDefinition->identifier,
                                    $contentType->identifier
                                );

                                $indexName = $this->fieldNameGenerator->getTypedName($fullName, $indexField->type);
                                $data[] = $indexName;
                            }
                        }
                        if (!$onlyFullText) {
                            $data[] = $this->fieldNameGenerator->getTypedName(
                                $this->fieldNameGenerator->getName('is_empty', $fieldDefinition->identifier),
                                new BooleanField()
                            );
                        }
                    }
                }
            }
        }

        return array_unique($data);
    }
}