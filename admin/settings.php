<?php

 add_action( 'admin_init', 'hkr_datatable_settings' );

 function hkr_datatable_settings() {

    register_setting( 'reading', 'hkr_category_datatable' );
    register_setting( 'reading', 'hkr_tag_datatable' );
    register_setting( 'reading', 'hkr_tax_datatable' );
    
    add_settings_section(
        'hkr_datatable_section',
        'Genesis Templates and wpDataTables',
        'hkr_datatable_section_text',
        'reading'
    );

    add_settings_field(
        'hkr_category_datatable',
        'Category DataTable Shortcode',
        'hkr_category_datatable_input',
        'reading',
        'hkr_datatable_section'
    );

    add_settings_field(
        'hkr_tag_datatable',
        'Tag DataTable Shortcode',
        'hkr_tag_datatable_input',
        'reading', 
        'hkr_datatable_section'
    );

    add_settings_field(
        'hkr_tax_datatable',
        'Custom Taxonomy DataTable Shortcode',
        'hkr_tax_datatable_input',
        'reading', 
        'hkr_datatable_section'
    );
 }

function hkr_datatable_section_text() {
    echo '<p>Override Genesis category and tag templates with a datatable.</p>';
}

function hkr_category_datatable_input() {
    echo '<input name="hkr_category_datatable" id="hkr_category_datatable" type="text" value="' . get_option( 'hkr_category_datatable' ) . '" />';
}

function hkr_tag_datatable_input() {
    echo '<input name="hkr_tag_datatable" id="hkr_tag_datatable" type="text" value="' . get_option( 'hkr_tag_datatable' ) . '" />';
}

function hkr_tax_datatable_input() {
    echo '<input name="hkr_tax_datatable" id="hkr_tax_datatable" type="text" value="' . get_option( 'hkr_tax_datatable' ) . '" />';
}