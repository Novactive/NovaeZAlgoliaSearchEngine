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
use eZ\Publish\Core\Repository\Values\Content\Content;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Just a command to help debugging when contributing
 */
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
            ->setHidden(true)
            ->setName(self::$defaultName)
            ->setHidden(true)
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
        $query->limit = 15;
        $query->filter = new Criterion\SectionId(1);

        $query->facetBuilders[] = new Query\FacetBuilder\ContentTypeFacetBuilder();
        $query->facetBuilders[] = new Query\FacetBuilder\SectionFacetBuilder(['name' => 'Section']);
        $query->sortClauses = [new Query\SortClause\ContentId()];

        $result = $this->repository->getSearchService()->findContent($query);

        if (0 === $result->totalCount) {
            $io->text('No Results found.');
        } else {

            $io->section('Results:');
            foreach ($result->searchHits as $searchHit) {
                /* @var Content $content */
                $content = $searchHit->valueObject;
                $output->writeln($content->getName());
            }
            $io->newLine();

            foreach ($result->facets as $facet) {
                $io->section('Facet - '.$facet->name.':');
                if (isset($facet->entries)) {
                    foreach ($facet->entries as $facetEntry => $number) {
                        $output->writeln("{$facetEntry} => {$number}");
                    }
                    $io->newLine();
                }
            }
            $io->newLine();

        }

        $io->success('Done.');

        return 0;
    }
}
