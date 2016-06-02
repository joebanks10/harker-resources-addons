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

function hkr_str_replace_last($search, $replace, $subject) {
    $pos = strrpos($subject, $search);

    if ($pos !== false) {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}
