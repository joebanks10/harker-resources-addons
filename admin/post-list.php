<?php

/* Preview File Link */
add_filter('post_row_actions','hkr_wpdm_row_actions', 10, 2);

function hkr_wpdm_row_actions($actions, $post){
    if ( $post->post_type == 'wpdmpro' ) {
        $actions['download'] = wpdm_get_download_link($post->ID);
    }
    return $actions;
}

/* Remove Custom Columns */
add_action( 'manage_wpdmpro_posts_columns' , 'hkr_wpdm_columns', 10, 2 );

function hkr_wpdm_columns( $columns ) {
    unset( $columns['shortcode'] );
    unset( $columns['image'] );
    $columns['owner'] = 'Owner';

    return $columns;
}

add_action( 'manage_wpdmpro_posts_custom_column' , 'hkr_wpdm_column_val', 10, 2 );

function hkr_wpdm_column_val( $column, $post_id ) {
    switch ( $column ) {
        case 'owner' :
            echo get_post_meta( $post_id, '_hkr_wpdm_owner', true );
            break;
    }
}

?>