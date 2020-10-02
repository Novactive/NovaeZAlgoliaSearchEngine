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

class MainCollector extends DataCollector
{
    /**
     * @var Logger
     */
    private $collector;

    public function __construct(Logger $collector)
    {
        $this->collector = $collector;
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        $logs = $this->collector->logs();
        $this->data = [
            'queryCount' => \count($logs),
            'queries' => $this->collector->logs()
        ];
    }

    public function getQueries(): array
    {
        return [
            'count' => $this->data['queryCount'],
            'list' => $this->data['queries']
        ];
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
