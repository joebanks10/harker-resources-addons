<?php 

// OLD: filter has been inserted in the wpDataTables plugin, 
// under the function wpdatatable_shortcode_handler, line 242
// add_filter( 'hkr_datatable_src_url', 'hkr_datatable_src_url_args');
add_filter( 'wpdatatables_filter_table_metadata', 'hkr_datatable_src_url_args');

function hkr_datatable_src_url_args( $table_data ) {
    $url = $table_data['content'];

    // get requested category and tag
    $category = get_query_var( 'cat' );
    $tag = get_query_var( 'tag' );
    $owner = get_query_var( 'owner' );

    $table_data['content'] = $url . "&cat=$category&tag=$tag&owner=$owner";

    return $table_data;
}

add_action( 'init', 'hkr_remove_genesis_loop' );

function hkr_remove_genesis_loop() {
    remove_action( 'genesis_loop', 'genesis_do_loop' ); 
}

add_action( 'genesis_loop', 'hkr_print_datatable' ); 

function hkr_print_datatable() {
    if ( is_category() || is_tag() || is_tax() ) {
        if ( is_category() ) {
            $shortcode = get_option( 'hkr_category_datatable' );
        } else if ( is_tag() ) {
            $shortcode = get_option( 'hkr_tag_datatable' );
        } else if ( is_tax() ) {
            $shortcode = get_option( 'hkr_tax_datatable' );
        }
        
        if ( empty( $shortcode ) ) {
            genesis_do_loop();
            return;
        }

        echo '<div class="hkr-wpdatatable">' . do_shortcode( $shortcode ) . '</div>';
    } else {
        genesis_do_loop();
    }
}

add_action( 'init', 'hkr_remove_category_title' );

function hkr_remove_category_title() {
    remove_action( 'genesis_before_loop', 'hkr_category_title', 1 );
}

add_action( 'genesis_before_loop', 'hkr_resource_category_title', 1 );

function hkr_resource_category_title() {
    if ( is_category() ) {
        $output = '<h1 class="resource-term-title">' . single_cat_title('', false) . ' Resources</h1>';
    } elseif ( is_tag() ) {
        $output = '<h1 class="resource-term-title">Resources Tagged: ' . single_cat_title('', false) . '</h1>';
    } elseif ( is_tax('owner') ) {
        $output = '<h1 class="resource-term-title">Resources Owned By: ' . single_cat_title('', false) . '</h1>';
    }

    if ( isset($output) ) {
        echo apply_filters( 'hkr_resource_term_title', $output );
    }
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

    @file_put_contents( plugin_dir_path( __FILE__ ) . '../data.php', $output );
}

function hkr_wpdm_get_data() {
    // get requested category and tag
    $category = get_query_var( 'cat' );
    $tag = get_query_var( 'tag' );
    $owner = get_query_var( 'owner' );

    // check for url arguments if empty
    $category = ( empty($category) && isset($_GET['cat']) ) ? $_GET['cat'] : $category;
    $tag = ( empty($tag) && isset($_GET['tag']) ) ? $_GET['tag'] : $tag;
    $owner = ( empty($owner) && isset($_GET['owner']) ) ? $_GET['owner'] : $owner;

    // query data
    $args = array( 
        "posts_per_page" => -1, 
        "post_type" => array( 'post', 'hkr_link', 'wpdmpro' ), 
        "category" => $category,
        "tag" => $tag
    );

    if ( $owner ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'owner',
                'field'    => 'slug',
                'terms'    => $owner
            )
        );
    }

    $entries = get_posts( $args );

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
        $post_categories = wp_get_post_terms( $entry->ID, array('category'), array('fields' => 'names') );
        $post_tags = wp_get_post_terms( $entry->ID, array('post_tag'), array('fields' => 'names') );
        $post_owners = wp_get_post_terms( $entry->ID, array('owner'), array('fields' => 'names') );

        if ( in_array('_outdated', $post_tags) ) {
            continue;
        }

        // filter out hidden terms
        $post_categories = array_filter( $post_categories, 'hkr_wpdm_filter_hidden_terms' );
        $post_tags = array_filter( $post_tags, 'hkr_wpdm_filter_hidden_terms' );
        $post_owners = array_filter( $post_owners, 'hkr_wpdm_filter_hidden_terms' );

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
            'Keywords' => '',
            'Owner' => join(', ', $post_owners ),
            'Modified' => $entry->post_modified,
            'Action' => $action_link
        );
    }

    return $output;
}

function hkr_wpdm_filter_hidden_terms( $val ) {
    return ( ! preg_match( '/^_/', $val ) );
}

add_filter( 'genesis_post_categories_shortcode', 'hkr_remove_underscores' );
add_filter( 'genesis_post_tags_shortcode', 'hkr_remove_underscores' );
add_filter( 'genesis_post_terms_shortcode', 'hkr_remove_underscores' );
add_filter( 'single_cat_title', 'hkr_remove_underscores' );
add_filter( 'single_tag_title', 'hkr_remove_underscores' );
add_filter( 'single_term_title', 'hkr_remove_underscores' );

function hkr_remove_underscores( $output ) {
    return str_replace( '_', '', $output );
}