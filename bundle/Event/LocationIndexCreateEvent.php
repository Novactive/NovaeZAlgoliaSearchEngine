<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Event;

use eZ\Publish\SPI\Persistence\Content\Location;
use Novactive\Bundle\eZAlgoliaSearchEngine\Mapping\LocationDocument;
use Symfony\Contracts\EventDispatcher\Event;

final class LocationIndexCreateEvent extends Event
{
    /**
     * @var Location
     */
    private $location;

    /**
     * @var LocationDocument
     */
    private $document;

    public function __construct(Location $location, LocationDocument $document)
    {
        $this->location = $location;
        $this->document = $document;
    }

    public function getContent(): Location
    {
        return $this->location;
    }

    public function getDocument(): LocationDocument
    {
        return $this->document;
    }
}