<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Command\Search;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

final class FindLocations extends Command
{
    protected static $defaultName = 'nova:ez:algolia:find:locations';

    /**
     * @var Repository
     */
    private $repository;

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Fetch the Locations by Query.');
    }

    /**
     * @required
     */
    public function setDependencies(Repository $repository): void
    {
        $this->repository = $repository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $query = new LocationQuery();

        //$query->filter = new Criterion\ContentTypeIdentifier('article');
        //$query->filter = new Criterion\Location\Depth(Criterion\Operator::GT, 2);
        $query->filter = new Criterion\Location\Priority(Criterion\Operator::LTE, 2);

        $query->query = new Criterion\MatchAll();
        $query->offset = 0;
        $query->limit = 10;

        $result = $this->repository->getSearchService()->findLocations($query);

        $io->section('Results:');
        foreach ($result->searchHits as $searchHit) {
            /* @var Location $location */
            $location = $searchHit->valueObject;
            $output->writeln($location->pathString);
        }
        $io->newLine();

        $io->success('Done.');

        return 0;
    }
}