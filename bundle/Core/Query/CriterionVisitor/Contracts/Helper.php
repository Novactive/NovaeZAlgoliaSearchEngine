<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor\Contracts;

trait Helper
{
    private function toString($value): string
    {
        switch (gettype($value)) {
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'double':
                return sprintf('%F', $value);

            default:
                return (string) $value;
        }
    }

    private function escapeQuote($string, $doubleQuote = false)
    {
        $pattern = ($doubleQuote ? '/("|\\\)/' : '/(\'|\\\)/');

        return preg_replace($pattern, '\\\$1', $string);
    }
}