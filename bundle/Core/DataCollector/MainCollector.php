<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Sebastien Morel <s.morel@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class MainCollector  extends DataCollector
{

    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {

    }

    public function reset(): void
    {
        $this->data = [];
    }

    public function getName(): string
    {
        return 'nova.ez.algolia.collector';
    }
}
