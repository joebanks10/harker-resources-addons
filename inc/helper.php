<?php

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
