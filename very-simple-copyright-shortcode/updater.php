<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define constants for version control
define( 'VSCS_PLUGIN_VERSION', '1.0' );
define( 'VSCS_PLUGIN_NAME', 'very-simple-copyright-shortcode' );
define( 'VSCS_GITHUB_URL', 'https://github.com/vascofialho-nl/very-simple-copyright-shortcode' );


// Check for updates from GitHub
function vscs_check_for_updates() {
    $response = wp_remote_get( VSCS_GITHUB_URL . '/releases/latest' );
    
    if ( is_wp_error( $response ) ) {
        return; // Exit if there's an error reaching GitHub
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( isset( $data['tag_name'] ) && version_compare( $data['tag_name'], VSCS_PLUGIN_VERSION, '>' ) ) {
        add_action( 'admin_notices', 'vscs_display_update_notice' );
    }
}
add_action( 'admin_init', 'vscs_check_for_updates' );

// Display an update notice in the dashboard
function vscs_display_update_notice() {
    echo '<div class="notice notice-warning is-dismissible">';
    echo '<p>A new version of Very Simple Copyright Shortcode is available. <a href="' . VSCS_GITHUB_URL . '/releases/latest" target="_blank">Update now</a>.</p>';
    echo '</div>';
}

/* EOF =============================================================================================================================================================== */