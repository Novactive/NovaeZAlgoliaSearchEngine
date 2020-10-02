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

    public function __construct(bool $enabled)
    {
        $this->enabled = $enabled;
    }


}
