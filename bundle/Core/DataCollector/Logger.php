<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Sebastien Morel <s.morel@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\DataCollector;

class Logger
{
    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var array
     */
    private $logs;

    public function __construct(bool $enabled = false)
    {
        $this->enabled = $enabled;
        $this->logs = [];
    }

    private function isEnabled(): bool
    {
        return $this->enabled === true;
    }

    public function addSearch(
        string $mode,
        string $languageCode,
        string $replicaName,
        string $query,
        array $requestOptions,
        array $results
    ): self {
        if (!$this->isEnabled()) {
            return $this;
        }

        $log = new Log();
        $log->fromSearch($mode, $languageCode, $replicaName, $query, $requestOptions, $results);
        $this->logs[] = $log;

        return $this;
    }

    public function addSave(string $mode, string $languageCode, string $replicaName, array $objects): self
    {
        if (!$this->isEnabled()) {
            return $this;
        }

        $log = new Log();
        $log->fromSave($mode, $languageCode, $replicaName, $objects);
        $this->logs[] = $log;

        return $this;
    }

    public function addDelete(string $mode, string $languageCode, string $replicaName, array $objects): self
    {
        if (!$this->isEnabled()) {
            return $this;
        }
        $log = new Log();
        $log->fromDelete($mode, $languageCode, $replicaName, $objects);
        $this->logs[] = $log;

        return $this;
    }

    public function addPurge(string $mode, string $replicaName): self
    {
        if (!$this->isEnabled()) {
            return $this;
        }
        $log = new Log();
        $log->setMethod($mode);
        $log->setMethod('purge');
        $log->setReplicaName($replicaName);
        $this->logs[] = $log;

        return $this;
    }

    public function startTime(float $time): void
    {
        if (!$this->isEnabled()) {
            return;
        }
        /** @var Log $last */
        $last = $this->logs[\count($this->logs) - 1];
        $last->setExecutionTime(microtime(true) - $time);
    }

    public function logs(): array
    {
        return $this->logs;
    }
}
