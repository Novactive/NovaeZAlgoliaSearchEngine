<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine;

use LogicException;
use Novactive\Bundle\eZAlgoliaSearchEngine\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NovaEzAlgoliaSearchEngine extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $extension = new Extension();
            if (!$extension instanceof ExtensionInterface) {
                $fqdn    = \get_class($extension);
                $message = 'Extension %s must implement %s.';
                throw new LogicException(sprintf($message, $fqdn, ExtensionInterface::class));
            }
            $this->extension = $extension;
        }

        return $this->extension;
    }
}