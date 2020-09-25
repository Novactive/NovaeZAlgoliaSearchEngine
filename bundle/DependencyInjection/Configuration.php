<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SAConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

final class Configuration extends SAConfiguration
{
    public const NAMESPACE = 'nova_ezalgoliasearchengine';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::NAMESPACE);
        $rootNode = $treeBuilder->getRootNode();
        $systemNode = $this->generateScopeBaseNode($rootNode);
        $systemNode
            ->scalarNode('index_name_prefix')->end()
            ->scalarNode('app_id')->end()
            ->scalarNode('api_secret_key')->end()
            ->scalarNode('api_search_only_key')->end()
            ->arrayNode('searchable_attributes')->prototype('scalar')->end()->end()
            ->arrayNode('attributes_for_faceting')->prototype('scalar')->end()->end()
            ->arrayNode('attributes_to_retrieve')->prototype('scalar')->end()->end()
            ->arrayNode('attributes_for_replicas')->prototype('scalar')->end()->end()
            ->arrayNode('exclude_content_types')->prototype('scalar')->end()->end()
            ->arrayNode('include_content_types')->prototype('scalar')->end()->end()
            ->end();

        return $treeBuilder;
    }
}