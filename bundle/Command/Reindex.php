<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Command;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Handler;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\Core\Repository\Values\Content\Location;

final class Reindex extends Command
{
    protected static $defaultName = 'nova:ez:algolia:reindex';

    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var PersistenceHandler
     */
    private $persistenceHandler;

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Reindex all Contents and Locations under eZ Platform folder.');
    }

    /**
     * @required
     */
    public function setDependencies(
        SearchService $searchService,
        Handler $handler,
        PersistenceHandler $persistenceHandler
    ): void {
        $this->searchService = $searchService;
        $this->handler = $handler;
        $this->persistenceHandler = $persistenceHandler;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $query = new LocationQuery();
        $query->query = new Criterion\MatchAll();
        $query->filter = new Criterion\ContentTypeIdentifier(['folder', 'article']);
        /* @var SearchHit $hit */
        foreach ($this->searchService->findLocations($query) as $hit) {
            /* @var Location $location */
            $location = $hit->valueObject;
            if (preg_match('#^/1/2/42/.+#', $location->pathString)) {
                $this->handler->indexLocation($this->persistenceHandler->locationHandler()->load($location->id));
                $this->handler->indexContent($this->persistenceHandler->contentHandler()->load($location->contentId));
                $io->section('Location: '.$location->id.'. Content: '.$location->contentId);
            }
        }

        $io->success('Done.');

        return 0;
    }
}