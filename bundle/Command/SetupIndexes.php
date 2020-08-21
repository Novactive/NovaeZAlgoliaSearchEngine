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
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query;
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
    public function setDependencies(AlgoliaClient $client, LanguageService $languageService): void
    {
        $this->client = $client;
        $this->languageService = $languageService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->languageService->loadLanguages() as $language) {
            $index = $this->client->getIndex($language->languageCode);
            $index->setSettings(['searchableAttributes' => Query::SEARCH_ATTRIBUTES]);
            $io->section('Index '.$index->getIndexName().' created.');
        }

        $io->success('Done.');

        return 0;
    }
}