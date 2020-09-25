<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core;

use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\SearchIndex;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Novactive\Bundle\eZAlgoliaSearchEngine\DependencyInjection\Configuration;
use eZ\Publish\Core\Repository\Permission\PermissionCriterionResolver;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\CriterionVisitor;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

final class AlgoliaClient
{
    /**
     * @var array
     */
    private $indexes;

    /**
     * @var array
     */
    private $config;

    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var PermissionCriterionResolver
     */
    private $permissionCriterionResolver;

    /**
     * @var CriterionVisitor
     */
    private $dispatcherCriterionVisitor;

    public function __construct(
        ConfigResolverInterface $configResolver,
        PermissionCriterionResolver $permissionCriterionResolver,
        CriterionVisitor $dispatcherCriterionVisitor
    ) {
        $this->configResolver = $configResolver;
        $this->permissionCriterionResolver = $permissionCriterionResolver;
        $this->dispatcherCriterionVisitor = $dispatcherCriterionVisitor;
        $this->config = [
            'index_name_prefix' => $this->configResolver->getParameter(
                'index_name_prefix',
                Configuration::NAMESPACE
            ),
            'app_id' => $this->configResolver->getParameter('app_id', Configuration::NAMESPACE),
            'api_secret_key' => $this->configResolver->getParameter('api_secret_key', Configuration::NAMESPACE),
            'api_search_only_key' => $this->configResolver->getParameter(
                'api_search_only_key',
                Configuration::NAMESPACE
            ),
        ];
    }

    public function getIndex(string $languageCode, string $mode = 'admin', ?string $replicaSuffix = null): SearchIndex
    {
        $indexName = $this->config['index_name_prefix'].'-'.$languageCode;

        if (null !== $replicaSuffix) {
            $indexName .= '-'.$replicaSuffix;
        }

        if (isset($this->indexes[$indexName])) {
            return $this->indexes[$indexName];
        }

        if (!\in_array($mode, ['admin', 'search'], true)) {
            throw new InvalidArgumentException('$mode', 'The Index mode must either "admin" or "search".');
        }
        $apiKey = ('admin' === $mode) ? $this->config['api_secret_key'] : $this->getSecuredApiKey();

        $client = SearchClient::create($this->config['app_id'], $apiKey);
        $this->indexes[$indexName] = $client->initIndex($indexName);

        return $this->indexes[$indexName];
    }

    public function getSecuredApiKey(): string
    {
        static $key;
        if (null === $key) {
            $restrictions = [];
            $permissionsCriterion = $this->permissionCriterionResolver->getPermissionsCriterion('content', 'read');
            if ($permissionsCriterion) {
                $restrictions['filters'] = $this->dispatcherCriterionVisitor->visit(
                    $this->dispatcherCriterionVisitor,
                    $permissionsCriterion
                );
            }

            $key = SearchClient::generateSecuredApiKey(
                $this->configResolver->getParameter('api_search_only_key', Configuration::NAMESPACE),
                $restrictions
            );
        }

        return $key;
    }
}