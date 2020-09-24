<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Event;

use eZ\Publish\SPI\Persistence\Content;
use Novactive\Bundle\eZAlgoliaSearchEngine\Mapping\Document;

final class ContentIndexCreateEvent extends DocumentCreateEvent
{
    /**
     * @var Content
     */
    private $content;

    public function __construct(Content $content, Document $document)
    {
        parent::__construct($document);
        $this->content = $content;
    }

    public function getContent(): Content
    {
        return $this->content;
    }
}