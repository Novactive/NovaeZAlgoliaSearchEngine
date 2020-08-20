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
use Novactive\Bundle\eZAlgoliaSearchEngine\Mapping\ContentDocument;
use Symfony\Contracts\EventDispatcher\Event;

final class ContentIndexCreateEvent extends Event
{
    private Content $content;

    private ContentDocument $document;

    public function __construct(Content $content, ContentDocument $document)
    {
        $this->content = $content;
        $this->document = $document;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getDocument(): ContentDocument
    {
        return $this->document;
    }
}