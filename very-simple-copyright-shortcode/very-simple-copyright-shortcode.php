<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/*
Plugin Name: Very Simple Copyright Shortcode
Plugin URI: http://www.vascofialho.nl
Description: Display copyright information with a shortcode.
Author: vascofmdc
Version: 0.1
Author URI: http://www.vascofialho.nl
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: very-simple-copyright-shortcode
*/


// Include installation scripts
require_once(plugin_dir_path(__FILE__) . 'install.php');

// Include updater system (connected with github)
require_once plugin_dir_path( __FILE__ ) . 'updater.php';
define( 'VSCS_PLUGIN_VERSION', '1.0' ); // Adjust this when you release a new version.
new VSCS_Plugin_Updater( __FILE__, VSCS_PLUGIN_VERSION );


// Add a menu item to the dashboard
	function vscs_add_menu_item() {
		add_options_page('Copyright Settings', 'Copyright Settings', 'manage_options', 'vscs_copyright_settings', 'vscs_render_settings_page');
	}
	add_action('admin_menu', 'vscs_add_menu_item');

// Add link to plugin settings in plugins page
	function vscs_add_settings_link($links) {
		$settings_link = '<a href="options-general.php?page=vscs_copyright_settings">' . __('Settings', 'very-simple-copyright-shortcode') . '</a>';
		array_unshift($links, $settings_link);
		return $links;
	}
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'vscs_add_settings_link');

// Validate user capabilities
	function vscs_validate_user() {
		if (!current_user_can('manage_options')) {
			wp_die( esc_attr( __('You do not have sufficient permissions to access this page.', 'very-simple-copyright-shortcode') ) );
		}
	}

// Render the settings page
	function vscs_render_settings_page() {
		vscs_validate_user(); // Add user validation check
		
		echo '<div class="wrap">';
		echo '    <h2>Copyright Settings</h2>';
		echo '	  <form method="post" action="options.php">';
				
				      settings_fields('vscs_copyright_settings');
					  do_settings_sections('vscs_copyright_settings');
					  wp_nonce_field('vscs_save_settings', 'vscs_nonce');
					  submit_button();
				
		echo '	</form>';		
		echo '</div><!-- end of wrap  -->';
		
		echo '<hr>';
		
		echo '<div class="wrap">';
		echo '    <p>like this plugin? help me maintain it and create much more by donating here: <a href="https://paypal.me/vascofialho">https://paypal.me/vascofialho</a></p>';
		echo '</div><!-- end of wrap -->';

	}


// Register settings and fields
	function vscs_register_settings() {
		register_setting('vscs_copyright_settings', 'vscs_copyYear');
		register_setting('vscs_copyright_settings', 'vscs_site_title');
		
		register_setting('vscs_copyright_settings', 'vscs_powered_by_text');
		register_setting('vscs_copyright_settings', 'vscs_powered_by_name');
		register_setting('vscs_copyright_settings', 'vscs_powered_by_url');
	}
	add_action('admin_init', 'vscs_register_settings');

// Add fields to the settings page
	function vscs_render_settings_fields() {
		add_settings_section('vscs_copyright_section', 'Copyright', 'vscs_render_section_description', 'vscs_copyright_settings');

		add_settings_field('vscs_copyYear',   		'Copyright Year:', 		'vscs_render_copyYear_field',   		'vscs_copyright_settings', 'vscs_copyright_section');
		add_settings_field('vscs_site_title', 		'Site Title:', 	  		'vscs_render_site_title_field', 		'vscs_copyright_settings', 'vscs_copyright_section');
		
		add_settings_field('vscs_powered_by_text', 'Powered by text:',     'vscs_render_powered_by_text_field', 	'vscs_copyright_settings', 'vscs_copyright_section');
		add_settings_field('vscs_powered_by_name', 'Powered by name:',     'vscs_render_powered_by_name_field', 	'vscs_copyright_settings', 'vscs_copyright_section');
		add_settings_field('vscs_powered_by_url', 	'Powered by website:',  'vscs_render_powered_by_url_field', 	'vscs_copyright_settings', 'vscs_copyright_section');
	}
	add_action('admin_init', 'vscs_render_settings_fields');

// Render section description with shortcode usage instructions
	function vscs_render_section_description() {
		echo '<p>'  . esc_attr( __('Configure copyright information. Use the shortcode', 'very-simple-copyright-shortcode') ) . ' <code>[vs_copyright]</code> ' . esc_attr( __('to display the copyright text.', 'very-simple-copyright-shortcode') );
		echo '<br>' . esc_attr( __('it will look like this:', 'very-simple-copyright-shortcode') ) . do_shortcode('&nbsp; [vs_copyright]') .' </p>';
		echo '<hr>';
	}

// Render Copy Year field
	function vscs_render_copyYear_field() {
		$copyYear = get_option('vscs_copyYear', 'yyyy');
		echo '<input type="number" maxlength="4" name="vscs_copyYear" value="' . esc_attr($copyYear) . '" />';
		echo '&nbsp; (ex, when was the site ever first launched?)';
	}

// Render Site Title field
	function vscs_render_site_title_field() {
		$siteTitle = get_option('vscs_site_title', get_bloginfo('name'));
		echo '<input type="text" name="vscs_site_title" value="' . esc_attr($siteTitle) . '" />';
		echo '&nbsp; (ex, site title or whatever other text related to this website.)';

	}

// Render Powered by text field
	function vscs_render_powered_by_text_field() {
		$poweredBy_text = get_option('vscs_powered_by_text', __('Powered by: ', 'very-simple-copyright-shortcode'));
		echo '<input type="text" name="vscs_powered_by_text" value="' . esc_attr($poweredBy_text) . '" />';
		echo '&nbsp; (ex, powered by: / built by: or whatever you feel comfortable when presenting yourself. )';		
	}

// Render Powered by name field
	function vscs_render_powered_by_name_field() {
		$poweredBy_name = get_option('vscs_powered_by_name', __('Your name', 'very-simple-copyright-shortcode'));
		echo '<input type="text" name="vscs_powered_by_name" value="' . esc_attr($poweredBy_name) . '" />';
		echo '&nbsp; (ex, your name, online handle or business name. )';		
	}


// Render Powered by url field
	function vscs_render_powered_by_url_field() {
		$poweredBy_url = get_option('vscs_powered_by_url', __('https://domainname.tld', 'very-simple-copyright-shortcode'));
		echo '<input type="url" name="vscs_powered_by_url" value="' . esc_url($poweredBy_url) . '" />';
		echo '&nbsp; (ex, your personal or business website (no e-mailaddress) )';		
	}

// Display copyright with shortcode
	function vscs_display_copyright() {
		$copyYear   		= esc_html(get_option('vscs_copyYear',  ' '));
		$curYear    		= esc_html(gmdate('Y'));
		
		$site_title 		= esc_html(get_option('vscs_site_title', get_bloginfo('name')));
		$site_url   		= site_url();
		
		$powered_by_text 	= esc_html(get_option('vscs_powered_by_text', __('Powered by text: ', 'very-simple-copyright-shortcode')));
		$powered_by_name 	= esc_html(get_option('vscs_powered_by_name', __('Powered by name: ', 'very-simple-copyright-shortcode')));
		$powered_by_url 	= esc_html(get_option('vscs_powered_by_url',  __('Powered by url:  ', 'very-simple-copyright-shortcode')));

		$copyright  = '|&nbsp;' . __('Copyright', 'very-simple-copyright-shortcode') . ' &copy; ' . $copyYear . (($copyYear != $curYear) ? '/' . $curYear : '');
		$copyright .= ' <a href="' . $site_url . '">' . $site_title . '</a> | ';
		$copyright .= $powered_by_text . ' <a href="' . $powered_by_url . '" target="_blank">' . $powered_by_name . '</a> | ';
		
		return $copyright;
	}
	add_shortcode('vs_copyright', 'vscs_display_copyright');


/* EOF =============================================================================================================================================================== */