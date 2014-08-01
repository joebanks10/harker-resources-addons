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

    $labels = array(
        'name'                       => _x( 'Owner', 'taxonomy general name' ),
        'singular_name'              => _x( 'Owner', 'taxonomy singular name' ),
        'search_items'               => __( 'Search Owners' ),
        'popular_items'              => __( 'Popular Owners' ),
        'all_items'                  => __( 'All Owners' ),
        'parent_item'                => null,
        'parent_item_colon'          => null,
        'edit_item'                  => __( 'Edit Owner' ),
        'update_item'                => __( 'Update Owner' ),
        'add_new_item'               => __( 'Add New Owner' ),
        'new_item_name'              => __( 'New Owner Name' ),
        'separate_items_with_commas' => __( 'Separate owners with commas' ),
        'add_or_remove_items'        => __( 'Add or remove owners' ),
        'choose_from_most_used'      => __( 'Choose from the most used owners' ),
        'not_found'                  => __( 'No owners found.' ),
        'menu_name'                  => __( 'Owners' ),
    );

    $args = array(
        'hierarchical'          => false,
        'labels'                => $labels,
        'public'                => false,
        'show_ui'               => true,
        'show_in_nav_menus'     => false,
        'show_tagcloud'         => false,
        'show_admin_column'     => true,
        'rewrite'               => array( 'slug' => 'owner' ),
    );

    register_taxonomy( 'owner', array( 'post', 'hkr_link', 'wpdmpro' ), $args );
}

add_action('do_meta_boxes', 'hkr_wpdm_tax_metabox_priority', 10, 3);

function hkr_wpdm_tax_metabox_priority( $post_type, $context, $post ) {

    if ( ! in_array( $post_type, array( 'post', 'hkr_link', 'wpdmpro' ))) {
        return;
    }

    if ( $context === 'side' ) {
        global $wp_meta_boxes;

        $ownerdiv = $wp_meta_boxes[$post_type][$context]['core']['tagsdiv-owner'];
        unset( $wp_meta_boxes[$post_type][$context]['core']['tagsdiv-owner'] );
        $wp_meta_boxes[$post_type][$context]['core'] = array('tagsdiv-owner' => $ownerdiv) + $wp_meta_boxes[$post_type][$context]['core'];
    }
}
