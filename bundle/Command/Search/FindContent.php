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

        //$query->filter = new Criterion\ContentId([57, 58]);

        //        $query->filter = new Criterion\LogicalAnd(
        //            [
        //                new Criterion\ContentTypeIdentifier(['article', 'folder']),
        //                new Criterion\LogicalOr(
        //                    [
        //                        new Criterion\SectionId(3),
        //                        new Criterion\LogicalAnd(
        //                            [
        //                                new Criterion\ContentTypeId(1),
        //                                new Criterion\ParentLocationId(42)
        //                            ]
        //                        )
        //                    ]
        //                )
        //            ]
        //        );

        //                $query->filter = new Criterion\LogicalNot(
        //                    new Criterion\LogicalAnd(
        //                        [
        //                            new Criterion\ContentTypeIdentifier(['folder']),
        //                            new Criterion\ContentId([57, 58])
        //                        ]
        //                    )
        //                );

        //        $query->filter = new Criterion\LogicalAnd(
        //            [
        //                new Criterion\LogicalNot(
        //                    new Criterion\ContentTypeIdentifier(['folder'])
        //                ),
        //                new Criterion\LogicalNot(
        //                    new Criterion\ContentId([56, 58])
        //                )
        //            ]
        //        );

//                $query->filter = new Criterion\CustomField(
//                    'article_author_count_i',
//                    Criterion\Operator::EQ,
//                    1
//                );

//        $query->filter = new Criterion\DateMetadata(
//            Criterion\DateMetadata::CREATED,
//            Criterion\Operator::BETWEEN,
//            [1598551911, 1598552352]
//        );

//        $query->filter = new Criterion\Field(
//            'title',
//            Criterion\Operator::EQ,
//            'New article 5'
//        );

//        $query->filter = new Criterion\FieldRelation(
//            'related_content',
//            Criterion\Operator::IN,
//            [56]
//        );



        //$query->filter = new Criterion\UserMetadata(Criterion\UserMetadata::GROUP, Criterion\Operator::EQ, 12);

        //$query->filter = new Criterion\Subtree('/1/2/42/57/');

        //$query->filter = new Criterion\MapLocationDistance('home_location', Criterion\Operator::EQ, 10, 37.7512306, -122.4584587);

        //$query->filter = new Criterion\MatchNone();

        //$query->filter = new Criterion\SectionIdentifier('standard');
        //$query->filter = new Criterion\RemoteId('15aa056813f55caf7f38c7251c1634cc');
        //$query->filter = new Criterion\ObjectStateId([1,2]);

        //$query->query = new Criterion\MatchAll();
        $query->offset = 0;
        $query->limit = 10;
        $query->facetBuilders[] = new ContentTypeFacetBuilder(['name' => 'ContentType']);

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

        }

        $io->success('Done.');

        return 0;
    }
}