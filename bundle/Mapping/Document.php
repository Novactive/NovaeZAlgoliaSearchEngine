<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Mapping;

use eZ\Publish\SPI\Search\Document as BaseDocument;

class Document extends BaseDocument
{
    /**
     * @var int
     */
    public $contentTypeId;
}