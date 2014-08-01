<?php

add_action( 'init', 'hkr_wpdm_links_init' );

function hkr_wpdm_links_init() {
    $labels = array(
        'name'               => _x( 'Links', 'post type general name', 'hkr-resources' ),
        'singular_name'      => _x( 'Link', 'post type singular name', 'hkr-resources' ),
        'menu_name'          => _x( 'Links', 'admin menu', 'hkr-resources' ),
        'name_admin_bar'     => _x( 'Link', 'add new on admin bar', 'hkr-resources' ),
        'add_new'            => _x( 'Add New', 'Link', 'hkr-resources' ),
        'add_new_item'       => __( 'Add New Link', 'hkr-resources' ),
        'new_item'           => __( 'New Link', 'hkr-resources' ),
        'edit_item'          => __( 'Edit Link', 'hkr-resources' ),
        'view_item'          => __( 'View Link', 'hkr-resources' ),
        'all_items'          => __( 'All Links', 'hkr-resources' ),
        'search_items'       => __( 'Search Links', 'hkr-resources' ),
        'parent_item_colon'  => __( 'Parent Links:', 'hkr-resources' ),
        'not_found'          => __( 'No links found.', 'hkr-resources' ),
        'not_found_in_trash' => __( 'No links found in Trash.', 'hkr-resources' )
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'link' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 15,
        'menu_icon'          => 'dashicons-admin-links',
        'supports'           => array( 'title', 'author', 'excerpt' )
    );

    register_post_type( 'hkr_link', $args );
}