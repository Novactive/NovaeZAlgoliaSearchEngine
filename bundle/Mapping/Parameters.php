<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Mapping;

final class Parameters
{
    public static function getReplicas(array $attributes): array
    {
        $replicas = [];
        foreach ($attributes as $attribute) {
            $replicas[] = [
                'key' => "sort_by_{$attribute}_asc",
                'condition' => "asc({$attribute})",
            ];
            $replicas[] = [
                'key' => "sort_by_{$attribute}_desc",
                'condition' => "desc({$attribute})",
            ];
        }

        return $replicas;
    }
}