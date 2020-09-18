<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\SPI\Persistence\Content\Type\Handler;
use Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\Contracts\CommonVisitor;

final class ContentTypeIdentifierVisitor implements CriterionVisitor
{
    use CommonVisitor;

    private const INDEX_FIELD = 'content_type_identifier_s';

    /**
     * @var Handler
     */
    private $contentTypeHandler;

    public function __construct(Handler $contentTypeHandler)
    {
        $this->contentTypeHandler = $contentTypeHandler;
    }

    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof ContentTypeIdentifier;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        $ids = [];
        foreach ($criterion->value as $identifier) {
            $ids[] = $this->contentTypeHandler->loadByIdentifier($identifier)->identifier;
        }

        return $this->visitValues($ids, self::INDEX_FIELD . ':"%s"', $additionalOperators);
    }
}
