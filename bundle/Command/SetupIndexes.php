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
use Novactive\Bundle\eZAlgoliaSearchEngine\DependencyInjection\Configuration;
use Novactive\Bundle\eZAlgoliaSearchEngine\Mapping\Parameters;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

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

    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

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
        LanguageService $languageService,
        ConfigResolverInterface $configResolver
    ): void {
        $this->client = $client;
        $this->attributeGenerator = $attributeGenerator;
        $this->languageService = $languageService;
        $this->configResolver = $configResolver;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $customSearchableattributes = $this->attributeGenerator->getCustomSearchableAttributes();

        foreach ($this->languageService->loadLanguages() as $language) {

            $index = $this->client->getIndex($language->languageCode);

            $replicas = Parameters::getReplicas(
                $this->configResolver->getParameter(
                    'attributes_for_replicas',
                    Configuration::NAMESPACE
                )
            );

            $attributesForFaceting = array_merge(
                array_map(
                    static function ($item) {
                        return "filterOnly({$item})";
                    },
                    $customSearchableattributes
                ),
                $this->configResolver->getParameter(
                    'attributes_for_faceting',
                    Configuration::NAMESPACE
                )
            );

            $index->setSettings(
                [
                    'searchableAttributes' => array_merge(
                        $customSearchableattributes,
                        $this->configResolver->getParameter(
                            'searchable_attributes',
                            Configuration::NAMESPACE
                        )
                    ),
                    'attributesForFaceting' => $attributesForFaceting,
                    'attributesToRetrieve' => ['*'],
                    'replicas' => array_map(
                        static function (string $suffix) use ($index) {
                            return "{$index->getIndexName()}-{$suffix}";
                        },
                        array_column($replicas, 'key')
                    ),
                ],
                ['forwardToReplicas' => true]
            );

            $io->section('Index '.$index->getIndexName().' created.');

            foreach ($replicas as $replicaItem) {
                $replica = $this->client->getIndex($language->languageCode, 'admin', $replicaItem['key']);
                $io->writeln('replica '.$replica->getIndexName().' set');
                $replica->setSettings(
                    [
                        'ranking' => array_merge(
                            [$replicaItem['condition']],
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