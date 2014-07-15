<?php

/*
Plugin Name: Download Manager Add-Ons
Plugin URI: http://www.wpdownloadmanager.com/
Description: Download Manager Add-ons developed by The Harker School
Author: Joe Banks
Version: 1.0
Author URI: http://www.harker.org/
*/

register_activation_hook( __FILE__, 'hkr_wpdm_activate' );

function hkr_wpdm_activate() {
    @file_put_contents( UPLOAD_DIR . 'index.php', '<?php // Silence is golden.' );
    @file_put_contents( UPLOAD_DIR . '.htaccess', '' );
}

/* Preview File Link */
add_filter('post_row_actions','hkr_wpdm_row_actions', 10, 2);

function hkr_wpdm_row_actions($actions, $post){
    if ( $post->post_type == 'wpdmpro' ) {
        $data = wpdm_custom_data( $post->ID );

        if ( !isset($data['files']) || count($data['files']) > 1 ) 
            return $actions;

        $path = content_url( '/uploads/download-manager-files/' );
        $file = $data['files'][0];

        $actions['view_file'] = '<a href="'.$path.$file.'" target="_blank">Preview File</a>';
    }
    return $actions;
}

/* Remove Custom Columns */
add_action( 'manage_wpdmpro_posts_columns' , 'hkr_wpdm_columns', 10, 2 );

function hkr_wpdm_columns( $columns ) {
    unset( $columns['shortcode'] );
    unset( $columns['image'] );

    return $columns;
}

/* View Files Meta Box */
add_action( 'add_meta_boxes', 'hkr_wpdm_add_view_files_meta_box' );

function hkr_wpdm_add_view_files_meta_box() {
    add_meta_box(
        'view-files',
        __( 'View Files', 'hkr_wpdm' ),
        'hkr_wpdm_view_files_meta_box',
        'wpdmpro', 
        'normal',
        'high'
    );
}

function hkr_wpdm_view_files_meta_box( $post ) {
    $data = wpdm_custom_data( $post->ID );
    $path = content_url( '/uploads/download-manager-files/' );
    $files = ( isset($data['files']) ) ? $data['files'] : array();

    echo '<ul class="view-files-list">';
    foreach( $files as $file ) {
        echo '<li><a href="'.$path.$file.'" target="_blank">'.$file.'</a></li>';
    }
    echo '</ul>';
}

?>