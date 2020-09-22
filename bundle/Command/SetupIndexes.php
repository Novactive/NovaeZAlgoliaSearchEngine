<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Command;

use Novactive\Bundle\eZAlgoliaSearchEngine\Core\AlgoliaClient;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\AttributeGenerator;
use Novactive\Bundle\eZAlgoliaSearchEngine\Mapping\Parameters;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use eZ\Publish\API\Repository\LanguageService;

final class SetupIndexes extends Command
{
    protected static $defaultName = 'nova:ez:algolia:indexes:setup';

    /**
     * @var AlgoliaClient
     */
    private $client;

    /**
     * @var AttributeGenerator
     */
    private $attributeGenerator;

    /**
     * @var LanguageService
     */
    private $languageService;

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
        AttributeGenerator $attributeGenerator,
        LanguageService $languageService
    ): void {
        $this->client = $client;
        $this->attributeGenerator = $attributeGenerator;
        $this->languageService = $languageService;
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

            $customSearchableattributes = $this->attributeGenerator->getCustomSearchableAttributes();
            $attributesForFaceting = array_merge(
                array_map(
                    static function ($item) {
                        return "filterOnly({$item})";
                    },
                    $customSearchableattributes
                ),
                Parameters::ATTRIBUTES_FOR_FACETING
            );

            $index->setSettings(
                [
                    'searchableAttributes' => array_merge($customSearchableattributes, Parameters::SEARCH_ATTRIBUTES),
                    'attributesForFaceting' => $attributesForFaceting,
                    'attributesToRetrieve' => ['*'],
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
}