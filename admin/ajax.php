<?php
/**
 * @package YMBESEO\Admin
 */

if ( ! defined( 'YMBESEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * @todo this whole thing should probably be a proper class.
 */

/**
 * Convenience function to JSON encode and echo resuls and then die
 *
 * @param array $results
 */
function ymbeseo_ajax_json_echo_die( $results ) {
	echo json_encode( $results );
	die();
}

/**
 * Function used from AJAX calls, takes it variables from $_POST, dies on exit.
 */
function ymbeseo_set_option() {
	if ( ! current_user_can( 'manage_options' ) ) {
		die( '-1' );
	}

	check_ajax_referer( 'ymbeseo-setoption' );

	$option = sanitize_text_field( filter_input( INPUT_POST, 'option' ) );
	if ( $option !== 'page_comments' ) {
		die( '-1' );
	}

	update_option( $option, 0 );
	die( '1' );
}

add_action( 'wp_ajax_ymbeseo_set_option', 'ymbeseo_set_option' );

/**
 * Function used to remove the admin notices for several purposes, dies on exit.
 */
function ymbeseo_set_ignore() {
	if ( ! current_user_can( 'manage_options' ) ) {
		die( '-1' );
	}

	check_ajax_referer( 'ymbeseo-ignore' );

	$ignore_key = sanitize_text_field( filter_input( INPUT_POST, 'option' ) );

	$options                          = get_option( 'ymbeseo' );
	$options[ 'ignore_' . $ignore_key ] = true;
	update_option( 'ymbeseo', $options );

	die( '1' );
}

add_action( 'wp_ajax_ymbeseo_set_ignore', 'ymbeseo_set_ignore' );

/**
 * Hides the after-update notification until the next update for a specific user.
 */
function ymbeseo_dismiss_about() {
	if ( ! current_user_can( 'manage_options' ) ) {
		die( '-1' );
	}

	check_ajax_referer( 'ymbeseo-dismiss-about' );

	update_user_meta( get_current_user_id(), 'ymbeseo_seen_about_version' , YMBESEO_VERSION );

	die( '1' );
}

add_action( 'wp_ajax_ymbeseo_dismiss_about', 'ymbeseo_dismiss_about' );

/**
 * Hides the default tagline notice for a specific user.
 */
function ymbeseo_dismiss_tagline_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		die( '-1' );
	}

	check_ajax_referer( 'ymbeseo-dismiss-tagline-notice' );

	update_user_meta( get_current_user_id(), 'ymbeseo_seen_tagline_notice', 'seen' );

	die( '1' );
}

add_action( 'wp_ajax_ymbeseo_dismiss_tagline_notice', 'ymbeseo_dismiss_tagline_notice' );

/**
 * Function used to delete blocking files, dies on exit.
 */
function ymbeseo_kill_blocking_files() {
	if ( ! current_user_can( 'manage_options' ) ) {
		die( '-1' );
	}

	check_ajax_referer( 'ymbeseo-blocking-files' );

	$message = 'There were no files to delete.';
	$options = get_option( 'ymbeseo' );
	if ( is_array( $options['blocking_files'] ) && $options['blocking_files'] !== array() ) {
		$message       = 'success';
		$files_removed = 0;
		foreach ( $options['blocking_files'] as $k => $file ) {
			if ( ! @unlink( $file ) ) {
				$message = __( 'Some files could not be removed. Please remove them via FTP.', 'ymbeseo' );
			}
			else {
				unset( $options['blocking_files'][ $k ] );
				$files_removed ++;
			}
		}
		if ( $files_removed > 0 ) {
			update_option( 'ymbeseo', $options );
		}
	}

	die( $message );
}

add_action( 'wp_ajax_ymbeseo_kill_blocking_files', 'ymbeseo_kill_blocking_files' );

/**
 * Used in the editor to replace vars for the snippet preview
 */
function ymbeseo_ajax_replace_vars() {
	global $post;
	check_ajax_referer( 'ymbeseo-replace-vars' );

	$post = get_post( intval( filter_input( INPUT_POST, 'post_id' ) ) );
	$omit = array( 'excerpt', 'excerpt_only', 'title' );
	echo ymbeseo_replace_vars( stripslashes( filter_input( INPUT_POST, 'string' ) ), $post, $omit );
	die;
}

add_action( 'wp_ajax_ymbeseo_replace_vars', 'ymbeseo_ajax_replace_vars' );

/**
 * Save an individual SEO title from the Bulk Editor.
 */
function ymbeseo_save_title() {
	ymbeseo_save_what( 'title' );
}

add_action( 'wp_ajax_ymbeseo_save_title', 'ymbeseo_save_title' );

/**
 * Save an individual meta description from the Bulk Editor.
 */
function ymbeseo_save_description() {
	ymbeseo_save_what( 'metadesc' );
}

add_action( 'wp_ajax_ymbeseo_save_metadesc', 'ymbeseo_save_description' );

/**
 * Save titles & descriptions
 *
 * @param string $what
 */
function ymbeseo_save_what( $what ) {
	check_ajax_referer( 'ymbeseo-bulk-editor' );

	$new      = filter_input( INPUT_POST, 'new_value' );
	$post_id  = intval( filter_input( INPUT_POST, 'ymbeseo_post_id' ) );
	$original = filter_input( INPUT_POST, 'existing_value' );

	$results = ymbeseo_upsert_new( $what, $post_id, $new, $original );

	ymbeseo_ajax_json_echo_die( $results );
}

/**
 * Helper function to update a post's meta data, returning relevant information
 * about the information updated and the results or the meta update.
 *
 * @param int    $post_id
 * @param string $new_meta_value
 * @param string $orig_meta_value
 * @param string $meta_key
 * @param string $return_key
 *
 * @return string
 */
function ymbeseo_upsert_meta( $post_id, $new_meta_value, $orig_meta_value, $meta_key, $return_key ) {

	$post_id                  = intval( $post_id );
	$sanitized_new_meta_value = wp_strip_all_tags( $new_meta_value );
	$orig_meta_value          = wp_strip_all_tags( $orig_meta_value );

	$upsert_results = array(
		'status'                 => 'success',
		'post_id'                => $post_id,
		"new_{$return_key}"      => $new_meta_value,
		"original_{$return_key}" => $orig_meta_value,
	);

	$the_post = get_post( $post_id );
	if ( empty( $the_post ) ) {

		$upsert_results['status']  = 'failure';
		$upsert_results['results'] = __( 'Post doesn\'t exist.', 'ymbeseo' );

		return $upsert_results;
	}

	$post_type_object = get_post_type_object( $the_post->post_type );
	if ( ! $post_type_object ) {

		$upsert_results['status']  = 'failure';
		$upsert_results['results'] = sprintf( __( 'Post has an invalid Post Type: %s.', 'ymbeseo' ), $the_post->post_type );

		return $upsert_results;
	}

	if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {

		$upsert_results['status']  = 'failure';
		$upsert_results['results'] = sprintf( __( 'You can\'t edit %s.', 'ymbeseo' ), $post_type_object->label );

		return $upsert_results;
	}

	if ( ! current_user_can( $post_type_object->cap->edit_others_posts ) && $the_post->post_author != get_current_user_id() ) {

		$upsert_results['status']  = 'failure';
		$upsert_results['results'] = sprintf( __( 'You can\'t edit %s that aren\'t yours.', 'ymbeseo' ), $post_type_object->label );

		return $upsert_results;

	}

	if ( $sanitized_new_meta_value === $orig_meta_value && $sanitized_new_meta_value !== $new_meta_value ) {
		$upsert_results['status']  = 'failure';
		$upsert_results['results'] = __( 'You have used HTML in your value which is not allowed.', 'ymbeseo' );

		return $upsert_results;
	}

	$res = update_post_meta( $post_id, $meta_key, $sanitized_new_meta_value );

	$upsert_results['status']  = ( $res !== false ) ? 'success' : 'failure';
	$upsert_results['results'] = $res;

	return $upsert_results;
}

/**
 * Save all titles sent from the Bulk Editor.
 */
function ymbeseo_save_all_titles() {
	ymbeseo_save_all( 'title' );
}

add_action( 'wp_ajax_ymbeseo_save_all_titles', 'ymbeseo_save_all_titles' );

/**
 * Save all description sent from the Bulk Editor.
 */
function ymbeseo_save_all_descriptions() {
	ymbeseo_save_all( 'metadesc' );
}

add_action( 'wp_ajax_ymbeseo_save_all_descriptions', 'ymbeseo_save_all_descriptions' );

/**
 * Utility function to save values
 *
 * @param string $what
 */
function ymbeseo_save_all( $what ) {
	check_ajax_referer( 'ymbeseo-bulk-editor' );

	// @todo the YMBESEO Utils class can't filter arrays in POST yet.
	$new_values      = $_POST['items'];
	$original_values = $_POST['existing_items'];

	$results = array();

	if ( is_array( $new_values ) && $new_values !== array() ) {
		foreach ( $new_values as $post_id => $new_value ) {
			$original_value = $original_values[ $post_id ];
			$results[]      = ymbeseo_upsert_new( $what, $post_id, $new_value, $original_value );
		}
	}
	ymbeseo_ajax_json_echo_die( $results );
}

/**
 * Insert a new value
 *
 * @param string $what
 * @param int    $post_id
 * @param string $new
 * @param string $original
 *
 * @return string
 */
function ymbeseo_upsert_new( $what, $post_id, $new, $original ) {
	$meta_key = YMBESEO_Meta::$meta_prefix . $what;

	return ymbeseo_upsert_meta( $post_id, $new, $original, $meta_key, $what );
}

/**
 * Create an export and return the URL
 */
function ymbeseo_get_export() {

	$include_taxonomy = ( filter_input( INPUT_POST, 'include_taxonomy' ) === 'true' );
	$export           = new YMBESEO_Export( $include_taxonomy );

	ymbeseo_ajax_json_echo_die( $export->get_results() );
}

add_action( 'wp_ajax_ymbeseo_export', 'ymbeseo_get_export' );

/**
 * Handles the posting of a new FB admin.
 */
function ymbeseo_add_fb_admin() {
	check_ajax_referer( 'ymbeseo_fb_admin_nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		die( '-1' );
	}

	$facebook_social = new Yoast_Social_Facebook();

	wp_die( $facebook_social->add_admin( filter_input( INPUT_POST, 'admin_name' ), filter_input( INPUT_POST, 'admin_id' ) ) );
}

add_action( 'wp_ajax_ymbeseo_add_fb_admin', 'ymbeseo_add_fb_admin' );

new Yoast_Dashboard_Widget();
