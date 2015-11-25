<?php
/**
 * @package YMBESEO\Admin
 * @since      1.8.0
 */

/**
 * Customizes user profile.
 */
class YMBESEO_Admin_User_Profile {
	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'show_user_profile', array( $this, 'user_profile' ) );
		add_action( 'edit_user_profile', array( $this, 'user_profile' ) );
		add_action( 'personal_options_update', array( $this, 'process_user_option_update' ) );
		add_action( 'edit_user_profile_update', array( $this, 'process_user_option_update' ) );
	}

	/**
	 * Filter POST variables.
	 *
	 * @param string $var_name
	 *
	 * @return mixed
	 */
	private function filter_input_post( $var_name ) {
		$val = filter_input( INPUT_POST, $var_name );
		if ( $val ) {
			return YMBESEO_Utils::sanitize_text_field( $val );
		}
		return '';
	}

	/**
	 * Updates the user metas that (might) have been set on the user profile page.
	 *
	 * @param    int $user_id of the updated user.
	 */
	public function process_user_option_update( $user_id ) {
		update_user_meta( $user_id, '_so_YMBESEO_profile_updated', time() );

		check_admin_referer( 'YMBESEO_user_profile_update', 'YMBESEO_nonce' );

		update_user_meta( $user_id, 'YMBESEO_title', $this->filter_input_post( 'YMBESEO_author_title' ) );
		update_user_meta( $user_id, 'YMBESEO_metadesc', $this->filter_input_post( 'YMBESEO_author_metadesc' ) );
		update_user_meta( $user_id, 'YMBESEO_metakey', $this->filter_input_post( 'YMBESEO_author_metakey' ) );
		update_user_meta( $user_id, 'YMBESEO_excludeauthorsitemap', $this->filter_input_post( 'YMBESEO_author_exclude' ) );
	}

	/**
	 * Add the inputs needed for SEO values to the User Profile page
	 *
	 * @param    object $user
	 */
	public function user_profile( $user ) {
		$options = YMBESEO_Options::get_all();

		wp_nonce_field( 'YMBESEO_user_profile_update', 'YMBESEO_nonce' );

		require_once( 'views/user-profile.php' );
	}

}
