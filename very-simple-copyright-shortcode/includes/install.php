<?php
// Check if this file is being accessed within the WordPress environment and exit if not.
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly 
    }

// Redirect to settings page upon activation
    function vscs_redirect_to_settings() {
        if (is_admin() && current_user_can('manage_options') && get_option('vscs_redirect_to_settings') != 'redirected') {
            update_option('vscs_redirect_to_settings', 'redirected');
            wp_redirect(admin_url('options-general.php?page=vscs_copyright_settings'));
            exit;
        }
    }
    add_action('admin_init', 'vscs_redirect_to_settings');

/* EOF =============================================================================================================================================================== */
