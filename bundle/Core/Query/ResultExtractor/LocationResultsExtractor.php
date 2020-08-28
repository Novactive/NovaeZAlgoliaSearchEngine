<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\ResultExtractor;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\ResultExtractor\FacetResultExtractor\FacetResultExtractor;

final class LocationResultsExtractor extends AbstractResultsExtractor
{
    public const LOCATION_ID_FIELD = 'location_id_i';

    /** @var LocationHandler */
    private $locationHandler;

    public function __construct(
        LocationHandler $locationHandler,
        FacetResultExtractor $facetResultExtractor,
        bool $skipMissingLocations = true
    ) {
        parent::__construct($facetResultExtractor, $skipMissingLocations);

        $this->locationHandler = $locationHandler;
    }

    protected function loadValueObject(array $document): ValueObject
    {
        return $this->locationHandler->load((int)$document[self::LOCATION_ID_FIELD]);
    }

    public function getExpectedSourceFields(): array
    {
        return [
            self::MATCHED_TRANSLATION_FIELD,
            self::LOCATION_ID_FIELD,
        ];
    }
}
