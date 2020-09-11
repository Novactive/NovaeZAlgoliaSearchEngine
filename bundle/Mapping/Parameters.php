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
    public const SEARCH_ATTRIBUTES = [
        'content_id_i',
        'content_remote_id_id',
        'content_name_s',
        'content_type_id_i',
        'content_type_name_s',
        'content_type_identifier_s',
        'content_type_group_id_mi',
        'content_owner_user_id_i',
        'content_owner_user_group_id_mi',
        'content_main_language_code_id',
        'content_always_available_b',
        'section_id_i',
        'section_identifier_id',
        'section_name_s',
        'content_modification_date_dt',
        'content_publication_date_dt',
        'object_state_id_mi',
        'content_language_codes_ms',
        'content_version_creator_user_id_i',
        'location_id_mi',
        'location_parent_id_mi',
        'location_remote_id_mid',
        'location_path_string_mid',
        'main_location_i',
        'main_location_parent_i',
        'main_location_remote_id_id',
        'main_location_visible_b',
        'main_location_path_id',
        'main_location_depth_i',
        'main_location_priority_i',
        'location_visible_b',
        'meta_indexed_language_code_s',
        'meta_indexed_is_main_translation_b',
        'meta_indexed_is_main_translation_and_always_available_b',
    ];

    public const ATTRIBUTES_FOR_FACETING = [
        'content_type_identifier_s',
        'doc_type_s',
        'filterOnly(location_path_string_mid)',
        'filterOnly(location_remote_id_mid)',
        'filterOnly(section_identifier_id)',
        'filterOnly(content_remote_id_id)',
        'filterOnly(content_language_codes_ms)',
        'filterOnly(location_visible_b)',
        'filterOnly(is_main_location_b)',
        'filterOnly(location_remote_id_id)',
        'filterOnly(path_string_id)',
        'filterOnly(invisible_b)'
    ];

    public const REPLICAS = [
        'sort_by_content_name_asc' => [
            'condition' => [
                'asc(content_name_s)'
            ],
            'label' => [
                'eng-GB' => 'Content Name Asc.'
            ]
        ],
        'sort_by_content_name_desc' => [
            'condition' => [
                'desc(content_name_s)'
            ],
            'label' => [
                'eng-GB' => 'Content Name Desc.'
            ]
        ],
        'sort_by_publication_date_asc' => [
            'condition' => [
                'asc(content_publication_date_dt)'
            ],
            'label' => [
                'eng-GB' => 'Publication Date Asc.'
            ]
        ],
        'sort_by_publication_date_desc' => [
            'condition' => [
                'desc(content_publication_date_dt)'
            ],
            'label' => [
                'eng-GB' => 'Publication Date Desc.'
            ]
        ],
    ];
}