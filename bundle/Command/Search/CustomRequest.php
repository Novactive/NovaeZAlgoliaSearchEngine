<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Command\Search;

use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Search\Search;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Search\SearchQueryFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use eZ\Publish\API\Repository\Values\Content\Query;

final class CustomRequest extends Command
{
    protected static $defaultName = 'nova:ez:algolia:custom:request';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var Search
     */
    private $searchService;

    /**
     * @var SearchQueryFactory
     */
    private $searchQueryFactory;

    /**
     * @required
     */
    public function setDependencies(Search $searchService, SearchQueryFactory $searchQueryFactory): void
    {
        $this->searchService = $searchService;
        $this->searchQueryFactory = $searchQueryFactory;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Send a custom request to Algolia using the Client Search method parameters.')
            ->addArgument('type', InputArgument::REQUIRED, 'Request type (content, location, raw)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        switch ($input->getArgument('type')) {
            case 'content':
                $this->contentSearch();
                break;
            case 'location':
                $this->locationSearch();
                break;
            case 'raw':
                $this->rawSearch();
                break;
            default:
                break;
        }

        $this->io->success('Done.');

        return 0;
    }

    private function contentSearch(): void
    {
        $query = $this->searchQueryFactory->create(
            '',
            'location_ancestors_path_string_mid:"/1/2/42/57/"',
            ['content_type_identifier_s']
        );
        $query->setRequestOption('aroundLatLng', '37.7512306, -122.4584587');
        $query->setRequestOption('aroundRadius', 3000);

        $result = $this->searchService->findContents(
            $query,
            [
                new Query\FacetBuilder\ContentTypeFacetBuilder(
                    ['name' => 'ContentType']
                )
            ]
        );

        if (0 === $result->totalCount) {
            $this->io->text('No Results found.');
        } else {

            $this->io->section('Results:');
            foreach ($result->searchHits as $searchHit) {
                $object = $searchHit->valueObject;
                if ($object instanceof Content) {
                    $name = $object->getName();
                } elseif ($object instanceof ContentInfo) {
                    $name = $object->name;
                } else {
                    $name = 'undefined value object';
                }
                $this->io->writeln($name);
            }
            $this->io->newLine();

            foreach ($result->facets as $facet) {
                $this->io->section('Facet - '.$facet->name.':');
                if (isset($facet->entries)) {
                    foreach ($facet->entries as $facetEntry => $number) {
                        $this->io->writeln("{$facetEntry} => {$number}");
                    }
                }
            }
            $this->io->newLine();

        }
    }

    private function locationSearch(): void
    {
        $query = $this->searchQueryFactory->create(
            '',
            'content_language_codes_ms:"eng-GB"',
        );
        $query->setReplicaByAttribute('location_id_i');

        $result = $this->searchService->findLocations($query);

        $this->io->section('Results:');
        foreach ($result->searchHits as $searchHit) {
            /* @var Location $location */
            $location = $searchHit->valueObject;
            $this->io->writeln($location->pathString);
        }
        $this->io->newLine();
    }

    private function rawSearch(): void
    {
        $facets = ['doc_type_s'];
        $query = $this->searchQueryFactory->create(
            'en',
            'content_type_identifier_s:"article"',
            $facets
        );
        $query->setRequestOption('attributesToRetrieve', ['content_name_s']);
        $searchResult = $this->searchService->find($query);
        $this->io->section('Results:');
        foreach ($searchResult->getIterator() as $hit) {
            $this->io->writeln($hit['objectID'].' -> '.$hit['content_name_s']);
        }
        $this->io->section('Facets:');
        foreach ($facets as $facet) {
            foreach ($searchResult->getFacets($facet) as $name => $value) {
                $this->io->writeln("{$name} => {$value}");
            }
        }

        $this->io->newLine();
    }
}