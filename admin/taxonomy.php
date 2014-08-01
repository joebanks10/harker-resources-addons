<?php

add_action( 'init', 'hkr_wpdm_taxonomies', 20 );

function hkr_wpdm_taxonomies() {

    // remove wpdmcategory
    register_taxonomy( 'wpdmcategory', '' );

    // re-register category and post tags to include links and downloads 
    // original code was copied from WordPress Core v3.9.1
    global $wp_rewrite;

    if ( ! did_action( 'init' ) ) {
        $rewrite = array( 'category' => false, 'post_tag' => false, 'post_format' => false );
    } else {

        /**
         * Filter the post formats rewrite base.
         *
         * @since 3.1.0
         *
         * @param string $context Context of the rewrite base. Default 'type'.
         */
        $post_format_base = apply_filters( 'post_format_rewrite_base', 'type' );
        $rewrite = array(
            'category' => array(
                'hierarchical' => true,
                'slug' => get_option('category_base') ? get_option('category_base') : 'category',
                'with_front' => ! get_option('category_base') || $wp_rewrite->using_index_permalinks(),
                'ep_mask' => EP_CATEGORIES,
            ),
            'post_tag' => array(
                'slug' => get_option('tag_base') ? get_option('tag_base') : 'tag',
                'with_front' => ! get_option('tag_base') || $wp_rewrite->using_index_permalinks(),
                'ep_mask' => EP_TAGS,
            ),
            'post_format' => $post_format_base ? array( 'slug' => $post_format_base ) : false,
        );
    }

    register_taxonomy( 'category', array('post', 'hkr_link', 'wpdmpro'), array(
        'hierarchical' => true,
        'query_var' => 'category_name',
        'rewrite' => $rewrite['category'],
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        '_builtin' => true,
    ) );

    register_taxonomy( 'post_tag', array('post', 'hkr_link', 'wpdmpro'), array(
        'hierarchical' => false,
        'query_var' => 'tag',
        'rewrite' => $rewrite['post_tag'],
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        '_builtin' => true,
    ) );
}
