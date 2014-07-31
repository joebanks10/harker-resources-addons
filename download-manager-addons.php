<?php

/*
Plugin Name: Download Manager Add-Ons for Harker
Plugin URI: http://www.wpdownloadmanager.com/
Description: Download Manager Add-ons developed by The Harker School
Author: Joe Banks
Version: 1.0
Author URI: http://www.harker.org/
*/

register_activation_hook( __FILE__, 'hkr_wpdm_activate' );

function hkr_wpdm_activate() {
    // do something
}

add_action( 'wp_enqueue_scripts', 'hkr_wpdm_enqueue' );

function hkr_wpdm_enqueue() {
    wp_enqueue_style( 'hkr-wpdm-style', plugins_url('css/style.css', __FILE__) );
}

add_action( 'wp_ajax_hkr_wpdm_datatable_src', 'hkr_wpdm_ajax_datatable_src' );
add_action( 'wp_ajax_nopriv_hkr_wpdm_datatable_src', 'hkr_wpdm_ajax_datatable_src' );

function hkr_wpdm_ajax_datatable_src(){
    $output = serialize( hkr_wpdm_get_data() );

    echo $output; die();
}

add_action( 'template_redirect', 'hkr_wpdm_datatable_src');

function hkr_wpdm_datatable_src() {
    $output = serialize( hkr_wpdm_get_data() );

    @file_put_contents( plugin_dir_path( __FILE__ ) . 'data.php', $output );
}

function hkr_wpdm_get_data() {
    // get requested category and tag
    $category = get_query_var( 'wpdmcategory' );
    $tag = get_query_var( 'tag' );

    // query data
    $downloads = get_posts( array( 
        "posts_per_page" => -1, 
        "post_type" => "wpdmpro", 
        "wpdmcategory" => $category,
        "tag" => $tag 
    ));

    $output = array();
    foreach( $downloads as $download ) {
        $url = hkr_wpdm_get_download_url( $download->ID );
        $download_link = ( empty($url) ) ? '||Expired' : $url . '||Download';

        $output[] = array(
            'Title' => get_permalink( $download->ID ) . '||' . $download->post_title,
            'Categories' => join(', ', hkr_wpdm_get_post_term_names( $download->ID, array('wpdmcategory'), array( 'Parents', 'Faculty &amp; Staff', 'Students' ) ) ),
            'Tags' => join(', ', hkr_wpdm_get_post_term_names( $download->ID, array('post_tag') ) ),
            'Last Modified' => $download->post_modified,
            'Download' => $download_link
        );
    }

    return $output;
}

function hkr_wpdm_get_download_url( $id ) {
    $download_link = wpdm_get_download_link( $id );
    preg_match( '/href=\'(.*?)\'/', $download_link, $match);
    $download_link = ( isset($match[1]) ) ? $match[1] : '';

    return $download_link;
}

function hkr_wpdm_get_term_slugs( $taxonomy ) {
    $terms = get_terms( $taxonomy );
    $term_slugs = array_map( function($term) {
        return $term->slug;
    }, $terms );

    return $term_slugs;
}

function hkr_wpdm_get_post_term_names( $id, $taxonomy, $exclude = array() ) {
    $term_names = wp_get_post_terms( $id, $taxonomy, array('fields' => 'names') );
    $term_names = array_filter( $term_names, function($val) use ($exclude) {
        return ( ! in_array($val, $exclude) );
    });

    return $term_names;
}

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

/* View Files Meta Box */
add_action( 'add_meta_boxes', 'hkr_wpdm_add_meta_boxes' );

function hkr_wpdm_add_meta_boxes() {
    add_meta_box(
        'view-files',
        __( 'View Files', 'hkr_wpdm' ),
        'hkr_wpdm_view_files_meta_box',
        'wpdmpro', 
        'normal',
        'high'
    );
    add_meta_box(
        'ownerdiv', 
        __('Owner'), 
        'hkr_wpdm_owner_meta_box', 
        null, 
        'normal', 
        'core'
    );
}

function hkr_wpdm_view_files_meta_box( $post ) {
    $package = get_post( $post->ID, ARRAY_A );
    $package = array_merge( $package, wpdm_custom_data( $package['ID'] ));
    
    $preview = wpdm_doc_preview( $package );

    if ( empty($preview) ) {
        echo '<p>Please attach the file(s) and save the package to view.</p>';
    }

    echo $preview;
}

function hkr_wpdm_owner_meta_box( $post ) {
    wp_nonce_field( 'hkr_wpdm_meta', 'hkr_wpdm_meta_nonce' );

    $owner = get_post_meta( $post->ID, '_hkr_wpdm_owner', true );
    if ( empty($owner) ) {
        global $user_ID;
        $user = get_userdata( $user_ID );
        $owner = $user->display_name;
    }
?>
<label class="screen-reader-text" for="hkr_wpdm_owner"><?php _e('Owner'); ?></label>
<input type="text" id="hkr_wpdm_owner" name="hkr_wpdm_owner" value="<?php echo $owner ?>" />
<?php
}

add_action( 'save_post', 'hkr_wpdm_save_metadata' );

function hkr_wpdm_save_metadata( $post_id ) {

    // Check if our nonce is set.
    if ( ! isset( $_POST['hkr_wpdm_meta_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['hkr_wpdm_meta_nonce'], 'hkr_wpdm_meta' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    /* OK, it's safe for us to save the data now. */
    
    if ( ! isset( $_POST['hkr_wpdm_owner'] ) ) {
        return;
    }

    if ( empty( $_POST['hkr_wpdm_owner'] ) ) {
        global $user_ID;
        $user = get_userdata( $user_ID );
        $owner = $user->display_name;
    } else {
        $owner = sanitize_text_field( $_POST['hkr_wpdm_owner'] );
    }

    // Update the meta field in the database.
    update_post_meta( $post_id, '_hkr_wpdm_owner', $owner );
}

?>