<?php
// Check if this file is being accessed within the WordPress environment and exit if not.
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }


// Delete plugin data on plugin deletion
function vscs_delete_plugin_data() {
    // Delete plugin options
    delete_option( 'vscs_copyYear' );
    delete_option( 'vscs_site_title' );
    delete_option( 'vscs_powered_by_text' );
    delete_option( 'vscs_powered_by_name' );
    delete_option( 'vscs_powered_by_url' );
    delete_option( 'vscs_redirect_to_settings' );

    // Remove shortcode from post content
    $args = array(
        's' => '[vs_copyright]', 	// Search for posts containing the shortcode
        'posts_per_page' => -1, 	// Get all posts
        'post_type' => 'any', 		// Check all post types
        'fields' => 'ids', 			// Only get post IDs
    );

    $posts = get_posts( $args );

    if ( ! empty( $posts ) ) {
        foreach ( $posts as $post_id ) {
            // Get the current content of the post
            $post_content = get_post_field( 'post_content', $post_id );

            // Remove the shortcode from the content
            $updated_content = str_replace( '[vs_copyright]', '', $post_content );

            // Update the post content
            $post_data = array(
                'ID'           => $post_id,
                'post_content' => $updated_content,
            );

            // Use wp_update_post to save the updated content
            $updated_post_id = wp_update_post( $post_data );
			//if ( is_wp_error( $updated_post_id ) ) {
			//	error_log( 'Failed to update post ID ' . $post_id . ': ' . $updated_post_id->get_error_message() );
			//}
        }
    }

    // Clear cache for plugin options
    wp_cache_delete( 'vscs_copyYear', 'options' );
    wp_cache_delete( 'vscs_site_title', 'options' );
    wp_cache_delete( 'vscs_powered_by_text', 'options' );
    wp_cache_delete( 'vscs_powered_by_name', 'options' );
    wp_cache_delete( 'vscs_powered_by_url', 'options' );
    wp_cache_delete( 'vscs_redirect_to_settings', 'options' );
}

// Call the delete function only on valid uninstall request
vscs_delete_plugin_data();

/* EOF =============================================================================================================================================================== */
