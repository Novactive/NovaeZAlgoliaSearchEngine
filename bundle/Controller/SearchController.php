<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Controller;

use Novactive\Bundle\eZAlgoliaSearchEngine\Core\AlgoliaClient;
use Novactive\Bundle\eZAlgoliaSearchEngine\DependencyInjection\Configuration;
use Novactive\Bundle\eZAlgoliaSearchEngine\Mapping\Parameters;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Serializer\SerializerInterface;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Search\SearchQueryFactory;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SearchController
{
    /**
     * @Template("@NovaEzAlgoliaSearchEngine/search.html.twig")
     */
    public function searchAction(
        SearchQueryFactory $searchQueryFactory,
        SerializerInterface $serializer,
        ConfigResolverInterface $configResolver,
        AlgoliaClient $algoliaClient,
        TranslatorInterface $translator
    ): array {
        $query = $searchQueryFactory->create();

        return [
            'query' => $serializer->serialize($query, 'json'),
            'replicas' => array_map(
                static function ($item) use ($translator) {
                    $item['label'] = $translator->trans($item['key'], [], 'novaezalgolia');

                    return $item;
                },
                Parameters::getReplicas(
                    $configResolver->getParameter(
                        'attributes_for_replicas',
                        Configuration::NAMESPACE
                    )
                )
            ),
            'config' => [
                'index_name_prefix' => $configResolver->getParameter('index_name_prefix', Configuration::NAMESPACE),
                'app_id' => $configResolver->getParameter('app_id', Configuration::NAMESPACE),
                'api_key' => $algoliaClient->getSecuredApiKey(),
            ]
        ];
    }
}
