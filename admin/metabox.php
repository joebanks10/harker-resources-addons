<?php

add_action( 'add_meta_boxes', 'hkr_wpdm_add_meta_boxes' );

function hkr_wpdm_add_meta_boxes() {
    add_meta_box(
        'hkr-preview',
        __( 'View Files', 'hkr-resources' ),
        'hkr_wpdm_view_files_meta_box',
        'wpdmpro', 
        'normal',
        'high'
    );
    add_meta_box(
        'hkr-owner', 
        __('Owner', 'hkr-resources'), 
        'hkr_wpdm_owner_meta_box', 
        array( 'post', 'wpdmpro', 'hkr_link' ),
        'normal', 
        'core'
    );
    add_meta_box(
        'hkr-address',
        __( 'Web Address', 'hkr-resources' ),
        'hkr_wpdm_url_meta_box',
        'hkr_link', 
        'normal',
        'high'
    );
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

    // save owner
    if ( isset($_POST['hkr_wpdm_owner']) ) {
        if ( empty( $_POST['hkr_wpdm_owner'] ) ) {
            global $user_ID;
            $user = get_userdata( $user_ID );
            $owner = $user->display_name;
        } else {
            $owner = sanitize_text_field( $_POST['hkr_wpdm_owner'] );
        }

        update_post_meta( $post_id, '_hkr_wpdm_owner', $owner );

        return;
    }

    // save url
    if ( $_POST['hkr_wpdm_url'] ) {
        $url = esc_url_raw( $_POST['hkr_wpdm_url'] );

        update_post_meta( $post_id, '_hkr_wpdm_url', $url );
    }

}

/* View Files Meta Box */
function hkr_wpdm_view_files_meta_box( $post ) {
    $package = get_post( $post->ID, ARRAY_A );
    $package = array_merge( $package, wpdm_custom_data( $package['ID'] ));
    
    $preview = wpdm_doc_preview( $package );

    if ( empty($preview) ) {
        echo '<p>Please attach the file(s) and save the package to view.</p>';
    }

    echo $preview;
}

/* Owner Meta Box */
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

/* URL Meta Box */
function hkr_wpdm_url_meta_box( $post ) {
    wp_nonce_field( 'hkr_wpdm_meta', 'hkr_wpdm_meta_nonce' );

    $url = get_post_meta( $post->ID, '_hkr_wpdm_url', true );
?>
<label class="screen-reader-text" for="hkr_wpdm_url"><?php _e('URL'); ?></label>
<input type="text" id="hkr_wpdm_url" name="hkr_wpdm_url" class="widefat" value="<?php echo $url ?>" />
<p>Example: <code>http://wordpress.org/</code> — don’t forget the <code>http://</code></p>
<?php
}

?>