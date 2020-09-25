<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension as BaseExtension;

final class Extension extends BaseExtension
{
    public function getAlias(): string
    {
        return Configuration::NAMESPACE;
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('default_settings.yaml');

        $processor = new ConfigurationProcessor($container, $this->getAlias());
        $processor->mapSetting('index_name_prefix', $config);
        $processor->mapSetting('app_id', $config);
        $processor->mapSetting('api_secret_key', $config);
        $processor->mapSetting('api_search_only_key', $config);

        $attributeParameters = [
            'searchable_attributes',
            'attributes_for_faceting',
            'attributes_to_retrieve',
            'attributes_for_replicas',
            'exclude_content_types',
            'include_content_types'
        ];
        foreach ($attributeParameters as $parameter) {
            $processor->mapConfig(
                $config,
                function ($scopeSettings, $currentScope, ContextualizerInterface $contextualizer) use ($parameter) {
                    if (\count($scopeSettings[$parameter]) > 0) {
                        $contextualizer->setContextualParameter(
                            $parameter,
                            $currentScope,
                            $scopeSettings[$parameter]
                        );
                    }
                }
            );
        }
    }
}