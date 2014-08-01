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

    @file_put_contents( plugin_dir_path( __FILE__ ) . 'data.php', $output );
}

function hkr_wpdm_get_data() {
    // get requested category and tag
    $category = get_query_var( 'wpdmcategory' );
    $tag = get_query_var( 'tag' );

    // check for url arguments if empty
    $category = ( empty($category) && isset($_GET['wpdmcategory']) ) ? $_GET['wpdmcategory'] : $category;
    $tag = ( empty($tag) && isset($_GET['tag']) ) ? $_GET['tag'] : $tag;

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
