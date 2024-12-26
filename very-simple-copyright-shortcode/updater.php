<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
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
        // Bail early if no transient or if 'checked' isn't set
        if (!is_object($transient) || !isset($transient->checked)) {
            return $transient;
        }

        // Plugin and repository details
        $repo = 'vascofialho-nl/very-simple-copyright-shortcode';
        $plugin_data = get_plugin_data($this->file);
        $local_version = $plugin_data['Version'];
        $remote_version = $this->get_latest_version($repo);

        // Debugging: Log the versions
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Local version: $local_version, Remote version: $remote_version");
        }

        // Only add the update if the remote version is greater than the local version
        if ($remote_version && version_compare($local_version, $remote_version, '<')) {
            $transient->response[$this->plugin] = (object) array(
                'slug'        => $this->basename,
                'new_version' => $remote_version,
                'url'         => "https://github.com/$repo",
                'package'     => "https://github.com/$repo/releases/download/v$remote_version/very-simple-copyright-shortcode.zip",
                'auto_update' => true,
            );
        } else {
            // Debugging: Log when no update is added
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("No update required: Local version is equal to or newer than the remote version.");
            }
        }

        return $transient;
    }

    private function get_latest_version($repo) {
        $response = wp_remote_get("https://api.github.com/repos/$repo/releases/latest");

        if (is_wp_error($response)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Error fetching latest version: " . $response->get_error_message());
            }
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response));
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("GitHub API Response: " . print_r($data, true));
        }

        return isset($data->tag_name) ? ltrim($data->tag_name, 'v') : false;
    }

    public function plugin_info($false, $action, $response) {
        if ($response->slug !== $this->basename) {
            return false;
        }

        $repo = 'vascofialho-nl/very-simple-copyright-shortcode';

        $response->name = 'Very Simple Copyright Shortcode';
        $response->slug = $this->basename;
        $response->version = $this->get_latest_version($repo);
        $response->author = '<a href="http://www.vascofialho.nl">vascofmdc</a>';
        $response->homepage = 'https://github.com/vascofialho-nl/very-simple-copyright-shortcode';
        $response->download_link = "https://github.com/$repo/releases/download/v{$response->version}/very-simple-copyright-shortcode.zip";
        $response->sections = array(
            'description'  => 'This plugin allows you to easily display copyright information on your WordPress site using a shortcode.',
            'installation' => '1. Upload the plugin files to the `/wp-content/plugins/` directory.<br>2. Activate the plugin.<br>3. Configure settings under "Copyright Settings."',
            'changelog'    => 'Version 1.3.1: update to the updater file.',
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
