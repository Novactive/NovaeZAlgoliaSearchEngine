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
use Novactive\Bundle\eZAlgoliaSearchEngine\Mapping\Document;

final class LocationIndexCreateEvent extends DocumentCreateEvent
{
    /**
     * @var Location
     */
    private $location;

    public function __construct(Location $location, Document $document)
    {
        parent::__construct($document);
        $this->location = $location;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }
}