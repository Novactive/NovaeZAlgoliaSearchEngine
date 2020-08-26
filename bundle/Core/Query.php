<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core;

final class Query
{
    public const SEARCH_ATTRIBUTES = [
        'content_id_i',
        'content_remote_id_id',
        'content_name_s',
        'content_type_id_i',
        'content_type_name_s',
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
        'title_is_empty_b',
        'article_title_value_s',
        'article_title_fulltext_fulltext',
        'short_title_is_empty_b',
        'intro_is_empty_b',
        'article_intro_value_s',
        'article_intro_fulltext_fulltext',
        'body_is_empty_b',
        'article_body_value_s',
        'article_body_fulltext_fulltext',
        'image_is_empty_b'
    ];

    public const ATTRIBUTES_FOR_FACETING = [
        'content_type_name_s'
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