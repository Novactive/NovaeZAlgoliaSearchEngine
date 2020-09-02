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

final class LanguageCodeVisitor implements CriterionVisitor
{
    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\LanguageCode &&
               \in_array(
                   $criterion->operator,
                   [
                       Criterion\Operator::EQ,
                       Criterion\Operator::IN
                   ],
                   true
               );
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        $languageCodeExpressions = array_map(
            static function ($value) use ($additionalOperators) {
                return $additionalOperators.'content_language_codes_ms:"'.$value.'"';
            },
            $criterion->value
        );

        /** @var Criterion\LanguageCode $criterion */
        if ($criterion->matchAlwaysAvailable) {
            $languageCodeExpressions[] = 'content_always_available_b:true';
        }

        return '('.implode('NOT ' === $additionalOperators ? ' AND ' : ' OR ', $languageCodeExpressions).')';
    }
}
