<?php

/* Preview File Link */
add_filter('post_row_actions','hkr_wpdm_row_actions', 10, 2);

function hkr_wpdm_row_actions($actions, $post){
    if ( $post->post_type == 'wpdmpro' ) {
        $actions['download'] = wpdm_get_download_link($post->ID);
    }
    return $actions;
}

/* Custom Columns */
add_action( 'manage_posts_columns' , 'hkr_wpdm_columns', 10, 2 );
add_action( 'manage_wpdmpro_posts_columns' , 'hkr_wpdm_columns', 10, 2 );

function hkr_wpdm_columns( $columns ) {
    unset( $columns['shortcode'] );
    unset( $columns['image'] );
    $columns['date'] = _x( 'Published', 'column name' );
    $columns['modified'] = _x( 'Modified', 'column name' );

    return $columns;
}

add_filter( 'manage_edit-post_sortable_columns', 'hkr_wpdm_sortable_columns' );
add_filter( 'manage_edit-hkr_link_sortable_columns', 'hkr_wpdm_sortable_columns' );
add_filter( 'manage_edit-wpdmpro_sortable_columns', 'hkr_wpdm_sortable_columns' );

function hkr_wpdm_sortable_columns( $columns ) {
    $columns['modified'] = 'modified';
 
    return $columns;
}

add_action( 'manage_posts_custom_column' , 'hkr_wpdm_column_val', 10, 2 );

function hkr_wpdm_column_val( $column, $post_id ) {
    if ( $column === 'modified' ) {
        $date = get_post_modified_time( 'Y/m/d', null, $post_id, true );

        echo "<abbr title=\"$date\">$date</abbr><br>Modified";
    }
}

add_action( 'pre_get_posts', 'hkr_wpdm_column_orderby' );

function hkr_wpdm_column_orderby( $query ) {
    if ( ! is_admin() ) {
        return;
    }
        
    $orderby = $query->get('orderby');

    if ( empty($orderby) ) {
        $query->set( 'orderby', 'modified' );
    }
}

?>