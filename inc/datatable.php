<?php 

add_action( 'wp_ajax_hkr_wpdm_datatable_src', 'hkr_wpdm_ajax_datatable_src' );
add_action( 'wp_ajax_nopriv_hkr_wpdm_datatable_src', 'hkr_wpdm_ajax_datatable_src' );

function hkr_wpdm_ajax_datatable_src(){
    $output = serialize( hkr_wpdm_get_data() );

    echo $output; die();
}

add_action( 'template_redirect', 'hkr_wpdm_datatable_src');

function hkr_wpdm_datatable_src() {
    $output = serialize( hkr_wpdm_get_data() );

    @file_put_contents( plugin_dir_path( __FILE__ ) . '../data.php', $output );
}

function hkr_wpdm_get_data() {
    // get requested category and tag
    $category = get_query_var( 'cat' );
    $tag = get_query_var( 'tag' );

    // check for url arguments if empty
    $category = ( empty($category) && isset($_GET['cat']) ) ? $_GET['cat'] : $category;
    $tag = ( empty($tag) && isset($_GET['tag']) ) ? $_GET['tag'] : $tag;

    // query data
    $entries = get_posts( array( 
        "posts_per_page" => -1, 
        "post_type" => array( 'post', 'hkr_link', 'wpdmpro' ), 
        "category" => $category,
        "tag" => $tag 
    ));

    $output = array();
    foreach( $entries as $entry ) {

        // get permalink
        if ( $entry->post_type == 'hkr_link' ) {
            $permalink = esc_url(get_post_meta( $entry->ID, '_hkr_wpdm_url', true ));
        } else {
            $permalink = esc_url(get_permalink( $entry->ID ));
        }

        // get action link
        if ( $entry->post_type == 'wpdmpro' ) {
            $action_link = esc_url(hkr_wpdm_get_download_url( $entry->ID )) . '||Download';
        } else {
            $action_link = '';
        }

        // get categories and tags
        $post_categories = hkr_wpdm_get_post_term_names( $entry->ID, array('category'), array( 'Parents', 'Faculty &amp; Staff', 'Students' ) );
        $post_tags = hkr_wpdm_get_post_term_names( $entry->ID, array('post_tag') );

        // append post type to $post_tags
        switch ( $entry->post_type ) {
            case 'post':
                $post_tags[] = 'Articles';
                break;
            case 'hkr_link':
                $post_tags[] = 'Links';
                break;
            case 'wpdmpro':
                $post_tags[] = 'Files';
        }

        $output[] = array(
            'Title' => $permalink . '||' . $entry->post_title,
            'Categories' => join(', ', $post_categories ),
            'Tags' => join(', ', $post_tags ),
            'Modified' => $entry->post_modified,
            'Action' => $action_link
        );
    }

    return $output;
}
