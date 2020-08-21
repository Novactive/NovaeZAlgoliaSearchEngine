<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core;

final class DocumentIdGenerator
{
    public function generateContentDocumentId(int $contentId, string $languageCode): string
    {
        return sprintf('content-%d-%s', $contentId, $languageCode);
    }

    public function generateLocationDocumentId(int $locationId, string $languageCode): string
    {
        return sprintf('location-%d-%s', $locationId, $languageCode);
    }
}
