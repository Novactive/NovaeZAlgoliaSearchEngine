<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\ResultExtractor;

use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\ResultExtractor\FacetResultExtractor\FacetResultExtractor;
use eZ\Publish\API\Repository\Values\ValueObject;

final class ContentResultsExtractor extends AbstractResultsExtractor
{
    public const CONTENT_ID_FIELD = 'content_id_i';

    /** @var ContentHandler */
    private $contentHandler;

    public function __construct(
        ContentHandler $contentHandler,
        FacetResultExtractor $facetResultExtractor,
        bool $skipMissingContentItems = true
    ) {
        parent::__construct($facetResultExtractor, $skipMissingContentItems);

        $this->contentHandler = $contentHandler;
    }

    protected function loadValueObject(array $document): ValueObject
    {
        return $this->contentHandler->loadContentInfo(
            (int)$document[self::CONTENT_ID_FIELD]
        );
    }

    public function getExpectedSourceFields(): array
    {
        return [
            self::MATCHED_TRANSLATION_FIELD,
            self::CONTENT_ID_FIELD,
        ];
    }
}
