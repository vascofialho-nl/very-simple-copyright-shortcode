<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class VSCS_Plugin_Updater {
    private $api_url = 'https://api.github.com/repos/vascofialho-nl/very-simple-copyright-shortcode/releases/latest';
    private $plugin_file;
    private $plugin_slug;
    private $version;

    public function __construct( $plugin_file, $version ) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename( $plugin_file );
        $this->version = $version;

        add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_for_update' ] );
        add_filter( 'plugins_api', [ $this, 'plugin_info' ], 20, 3 );
    }

    public function check_for_update( $transient ) {
        if ( empty( $transient->checked ) ) return $transient;

        $current_version = $this->version;
        $remote_version = $this->get_latest_version();

        if ( version_compare( $current_version, $remote_version, '<' ) ) {
            $plugin_data = get_plugin_data( $this->plugin_file );
            $transient->response[ $this->plugin_slug ] = (object) [
                'slug'        => $this->plugin_slug,
                'new_version' => $remote_version,
                'package'     => $this->get_latest_download_url(),
                'url'         => $plugin_data['PluginURI'],
            ];
        }
        return $transient;
    }

    private function get_latest_version() {
        $response = wp_remote_get( $this->api_url );
        if ( is_wp_error( $response ) ) return false;

        $data = json_decode( wp_remote_retrieve_body( $response ) );
        return ! empty( $data->tag_name ) ? $data->tag_name : false;
    }

    private function get_latest_download_url() {
        $response = wp_remote_get( $this->api_url );
        if ( is_wp_error( $response ) ) return false;

        $data = json_decode( wp_remote_retrieve_body( $response ) );
        return ! empty( $data->assets[0]->browser_download_url ) ? $data->assets[0]->browser_download_url : false;
    }
}

new VSCS_Plugin_Updater( __FILE__, '1.0' ); // Replace '1.0' with the current plugin version.

/* EOF =============================================================================================================================================================== */