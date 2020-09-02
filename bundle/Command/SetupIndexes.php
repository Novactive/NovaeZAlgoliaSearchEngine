<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Command;

use eZ\Publish\SPI\Search\FieldType\BooleanField;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\AlgoliaClient;
use Novactive\Bundle\eZAlgoliaSearchEngine\Mapping\Parameters;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\Core\Search\Common\FieldRegistry;

final class SetupIndexes extends Command
{
    protected static $defaultName = 'nova:ez:algolia:indexes:setup';

    /**
     * @var AlgoliaClient
     */
    private $client;

    /**
     * @var LanguageService
     */
    private $languageService;

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

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Set up Algolia Indexes.');
    }

    /**
     * @required
     */
    public function setDependencies(
        AlgoliaClient $client,
        LanguageService $languageService,
        ContentTypeService $contentTypeService,
        FieldNameGenerator $fieldNameGenerator,
        FieldRegistry $fieldRegistry
    ): void {
        $this->client = $client;
        $this->languageService = $languageService;
        $this->contentTypeService = $contentTypeService;
        $this->fieldNameGenerator = $fieldNameGenerator;
        $this->fieldRegistry = $fieldRegistry;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->languageService->loadLanguages() as $language) {

            $index = $this->client->getIndex($language->languageCode);

            $replicaNames = array_map(
                static function (string $suffix) use ($index) {
                    return "{$index->getIndexName()}-{$suffix}";
                },
                array_keys(Parameters::REPLICAS)
            );

            $attributes = $this->getSearchableAttributes();
            $attributesForFaceting = array_merge(
                array_map(
                    static function ($item) {
                        return "filterOnly({$item})";
                    },
                    $attributes
                ),
                Parameters::ATTRIBUTES_FOR_FACETING
            );

            $index->setSettings(
                [
                    'searchableAttributes' => array_merge($attributes, Parameters::SEARCH_ATTRIBUTES),
                    'attributesForFaceting' => $attributesForFaceting,
                    'replicas' => $replicaNames,
                ],
                ['forwardToReplicas' => true]
            );

            $io->section('Index '.$index->getIndexName().' created.');

            foreach (Parameters::REPLICAS as $replicaSuffix => $attributes) {
                $replica = $this->client->getIndex($language->languageCode, $replicaSuffix);
                $io->writeln('replica '.$replica->getIndexName().' set');
                $replica->setSettings(
                    [
                        'ranking' => array_merge(
                            $attributes['condition'],
                            [
                                'typo',
                                'words',
                                'proximity',
                                'attribute',
                                'exact',
                            ]
                        ),
                    ]
                );
            }
        }

        $io->success('Done.');

        return 0;
    }

    private function getSearchableAttributes(): array
    {
        $data = [];
        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            foreach ($this->contentTypeService->loadContentTypes($contentTypeGroup) as $contentType) {
                /* @var FieldDefinition $fieldDefinition */
                foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
                    if ($fieldDefinition->isSearchable) {
                        $indexFields = $this->fieldRegistry->getType($fieldDefinition->fieldTypeIdentifier)
                                                           ->getIndexDefinition();
                        foreach ($indexFields as $key => $indexField) {
                            $fullName = $this->fieldNameGenerator->getName(
                                $key,
                                $fieldDefinition->identifier,
                                $contentType->identifier
                            );
                            $indexName = $this->fieldNameGenerator->getTypedName($fullName, $indexField);
                            $data[] = $indexName;
                        }
                        $data[] = $this->fieldNameGenerator->getTypedName(
                            $this->fieldNameGenerator->getName('is_empty', $fieldDefinition->identifier),
                            new BooleanField()
                        );
                    }
                }
            }
        }

        return array_unique($data);
    }
}