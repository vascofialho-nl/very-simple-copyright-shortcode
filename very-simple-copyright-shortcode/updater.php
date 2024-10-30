<?php
// Check if this file is being accessed within the WordPress environment and exit if not.
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class VSCS_Plugin_Updater {
    private $file;
    private $plugin;
    private $basename;
    private $active;

    public function __construct($file) {
        $this->file = $file;
        $this->plugin = plugin_basename($file);
        $this->basename = dirname($this->plugin);
        $this->active = is_plugin_active($this->plugin);

        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
    }

    public function check_for_update($transient) {
        if (!isset($transient->checked)) {
            return $transient;
        }

        // GitHub repository info
        $repo = 'vascofialho-nl/very-simple-copyright-shortcode';
        $remote_version = $this->get_latest_version($repo);

        $plugin_data = get_plugin_data($this->file);
        $local_version = $plugin_data['Version'];

        if (version_compare($local_version, $remote_version, '<')) {
            // Construct the update URL using the latest release version
            $update_url = "https://github.com/$repo/releases/download/v$remote_version/very-simple-copyright-shortcode.zip";

            $transient->response[$this->plugin] = (object) array(
                'slug'        => $this->basename,
                'new_version' => $remote_version,
                'url'         => "https://github.com/$repo",
                'package'     => $update_url,
            );
        }

        return $transient;
    }

    private function get_latest_version($repo) {
        $response = wp_remote_get("https://api.github.com/repos/$repo/releases/latest");
        if (is_wp_error($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response));
        return isset($data->tag_name) ? ltrim($data->tag_name, 'v') : false;
    }

    public function plugin_info($false, $action, $response) {
        if ($response->slug !== $this->basename) {
            return false;
        }

        // Custom plugin information
        $response->name = 'Very Simple Copyright Shortcode';
        $response->slug = $this->basename;
        $response->version = $this->get_latest_version('vascofialho-nl/very-simple-copyright-shortcode');
        $response->author = '<a href="http://www.vascofialho.nl">vascofmdc</a>';
        $response->homepage = 'https://github.com/vascofialho-nl/very-simple-copyright-shortcode';
        $response->download_link = "https://github.com/vascofialho-nl/very-simple-copyright-shortcode/archive/refs/tags/v{$response->version}.zip";

        // Adding the sections property
        $response->sections = array(
            'description'  => 'This plugin allows you to easily display copyright information on your WordPress site using a shortcode.',
            'installation' => '1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.<br>2. Activate the plugin through the \'Plugins\' screen in WordPress.<br>3. Navigate to \'Copyright Settings\' under the \'Settings\' menu in the WordPress dashboard to configure the plugin settings.<br>4. Use the `[vs_copyright]` shortcode to display the copyright information on your site.',
            'changelog'    => 'Version 1.2: Minor bug fixes and improvements.',
        );

        return $response;
    }

    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;
        $install_dir = plugin_dir_path($this->file);

        $wp_filesystem->move($result['destination'], $install_dir);
        $result['destination'] = $install_dir;

        if ($this->active) {
            activate_plugin($this->plugin);
        }

        return $result;
    }
}

// Initialize the updater
if (is_admin()) {
    $updater = new VSCS_Plugin_Updater(__FILE__);
}
