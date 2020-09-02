<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use eZ\Publish\SPI\Persistence\Content\Type\Handler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

final class ContentTypeIdentifierVisitor implements CriterionVisitor, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const INDEX_FIELD = 'content_type_identifier_s';

    /**
     * @var Handler
     */
    private $contentTypeHandler;

    public function __construct(Handler $contentTypeHandler)
    {
        $this->contentTypeHandler = $contentTypeHandler;
        $this->logger = new NullLogger();
    }

    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof ContentTypeIdentifier;
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        $validIds = [];

        $invalidIdentifiers = [];
        foreach ($criterion->value as $identifier) {
            try {
                $validIds[] = $this->contentTypeHandler->loadByIdentifier($identifier)->identifier;
            } catch (NotFoundException $e) {
                // Filter out non-existing content types, but track for code below
                $invalidIdentifiers[] = $identifier;
            }
        }

        if (count($invalidIdentifiers) > 0) {
            $this->logger->warning(
                sprintf(
                    'Invalid content type identifiers provided for ContentTypeIdentifier criterion: %s',
                    implode(', ', $invalidIdentifiers)
                )
            );
        }

        if (count($validIds) === 0) {
            return '(NOT *:*)';
        }

        return '('.
               implode(
                   'NOT ' === $additionalOperators ? ' AND ' : ' OR ',
                   array_map(
                       static function ($value) use ($additionalOperators) {
                           return $additionalOperators.self::INDEX_FIELD.':"'.$value.'"';
                       },
                       $validIds
                   )
               ).
               ')';
    }
}
