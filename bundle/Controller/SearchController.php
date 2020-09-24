<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Serializer\SerializerInterface;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Search\SearchQueryFactory;

class SearchController
{
    /**
     * @Template("@NovaEzAlgoliaSearchEngine/search.html.twig")
     */
    public function searchAction(SearchQueryFactory $searchQueryFactory, SerializerInterface $serializer): array
    {
//        $query = $searchQueryFactory->create(
//            '',
//            'section_id_i=1',
//            ['content_type_identifier_s'],
//            0,
//            5
//        );
        //$query->setReplica('sort_by_content_name_s_asc');
        //$query->setRequestOption('attributesToRetrieve', ['content_name_s']);
        $query = $searchQueryFactory->create();

        return ['query' => $serializer->serialize($query, 'json')];
    }
}
