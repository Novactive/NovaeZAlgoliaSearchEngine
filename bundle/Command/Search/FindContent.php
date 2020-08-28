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
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\ContentTypeFacetBuilder;
use eZ\Publish\Core\Repository\Values\Content\Content;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\Facet;

final class FindContent extends Command
{
    protected static $defaultName = 'nova:ez:algolia:find:content';

    /**
     * @var Repository
     */
    private $repository;

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Fetch the Content by Query.');
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

        $query = new Query();
        $query->filter = new Criterion\ContentTypeIdentifier('article');
        $query->query = new Criterion\MatchAll();
        $query->offset = 0;
        $query->limit = 10;
        $query->facetBuilders[] = new ContentTypeFacetBuilder(['name' => 'ContentType']);

        $result = $this->repository->getSearchService()->findContent($query);

        $io->section('Results:');
        foreach ($result->searchHits as $searchHit) {
            /* @var Content $content */
            $content = $searchHit->valueObject;
            $output->writeln($content->getName());
        }
        $io->newLine();

        /* @var Facet $facet */
        foreach ($result->facets as $facet) {
            $io->section('Facet - '.$facet->name.':');
            if (isset($facet->entries)) {
                foreach ($facet->entries as $facetEntry => $number) {
                    $output->writeln("{$facetEntry} => {$number}");
                }
            }
        }
        $io->newLine();

        $io->success('Done.');

        return 0;
    }
}