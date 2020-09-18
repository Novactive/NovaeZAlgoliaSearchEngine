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
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\ClientService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CustomRequest extends Command
{
    protected static $defaultName = 'nova:ez:algolia:custom:request';

    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @var SymfonyStyle
     */
    private $io;

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Send a custom request to Algolia using the Client Search method parameters.')
            ->addArgument('type', InputArgument::REQUIRED, 'Request type (content, location, raw)');
    }

    /**
     * @required
     */
    public function setDependencies(ClientService $clientService): void
    {
        $this->clientService = $clientService;
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
        $requestOptions = [
            'filters' => 'content_type_identifier_s:article',
            'attributesToHighlight' => [],
            'offset' => 0,
            'length' => 10,
            'aroundLatLng' => '37.7512306, -122.4584587',
            'aroundRadius' => 3000
        ];

        $result = $this->clientService->contentSearch('eng-GB', null, '', $requestOptions);

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
        $requestOptions = [
            'filters' => 'content_type_identifier_s:article',
            'attributesToHighlight' => [],
            'offset' => 0,
            'length' => 5,
        ];

        $result = $this->clientService->locationSearch('eng-GB', null, '', $requestOptions);

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
        $requestOptions = [
            'filters' => 'doc_type_s:content AND short_title_is_empty_b:false',
            'attributesToHighlight' => [],
            'offset' => 0,
            'length' => 10,
            'attributesToRetrieve' => ['*']
        ];

        $result = $this->clientService->rawSearch('eng-GB', null, '', $requestOptions);

        $this->io->section('Results:');
        foreach ($result['hits'] as $hit) {
            $this->io->writeln($hit['objectID']);
        }
        $this->io->newLine();
    }
}