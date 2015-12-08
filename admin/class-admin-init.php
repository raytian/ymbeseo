<?php
/**
 * @package YMBESEO\Admin
 */

/**
 * Performs the load on admin side.
 */
class YMBESEO_Admin_Init {

	/**
	 * Holds the YMBE SEO Options
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Holds the global `$pagenow` variable's value.
	 *
	 * @var string
	 */
	private $pagenow;

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->options = YMBESEO_Options::get_all();

		$GLOBALS['ymbeseo_admin'] = new YMBESEO_Admin;

		$this->pagenow = $GLOBALS['pagenow'];

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dismissible' ) );
		add_action( 'admin_init', array( $this, 'after_update_notice' ), 15 );
		add_action( 'admin_init', array( $this, 'tagline_notice' ), 15 );
		add_action( 'admin_init', array( $this, 'ga_compatibility_notice' ), 15 );

		$this->load_meta_boxes();
		$this->load_taxonomy_class();
		$this->load_admin_page_class();
		$this->load_admin_user_class();
		$this->load_xml_sitemaps_admin();
	}

	/**
	 * For WP versions older than 4.2, this includes styles and a script to make notices dismissible.
	 */
	public function enqueue_dismissible() {
		if ( version_compare( $GLOBALS['wp_version'], '4.2', '<' ) ) {
			wp_enqueue_style( 'ymbeseo-dismissible', plugins_url( 'css/ymbeseo-dismissible' . YMBESEO_CSSJS_SUFFIX . '.css', YMBESEO_FILE ), array(), YMBESEO_VERSION );
			wp_enqueue_script( 'ymbeseo-dismissible', plugins_url( 'js/ymbeseo-dismissible' . YMBESEO_CSSJS_SUFFIX . '.js', YMBESEO_FILE ), array( 'jquery' ), YMBESEO_VERSION, true );
		}
	}
	/**
	 * Redirect first time or just upgraded users to the about screen.
	 */
	public function after_update_notice() {
		if ( current_user_can( 'manage_options' ) && ! $this->seen_about() ) {

			if ( filter_input( INPUT_GET, 'intro' ) === '1' ) {
				update_user_meta( get_current_user_id(), 'ymbeseo_seen_about_version' , YMBESEO_VERSION );

				return;
			}
			/* translators: %1$s expands to YMBE SEO, $2%s to the version number, %3$s and %4$s to anchor tags with link to intro page  */
			$info_message = sprintf(
				__( '%1$s has been updated to version %2$s. %3$sClick here%4$s to find out what\'s new!', 'ymbeseo' ),
				'YMBE SEO',
				YMBESEO_VERSION,
				'<a href="' . admin_url( 'admin.php?page=ymbeseo_dashboard&intro=1' ) . '">',
				'</a>'
			);

			$notification_options = array(
				'type' => 'updated',
				'id' => 'ymbeseo-dismiss-about',
				'nonce' => wp_create_nonce( 'ymbeseo-dismiss-about' ),
			);

			Yoast_Notification_Center::get()->add_notification( new Yoast_Notification( $info_message, $notification_options ) );
		}
	}

	/**
	 * Helper to verify if the current user has already seen the about page for the current version
	 *
	 * @return bool
	 */
	private function seen_about() {
		return get_user_meta( get_current_user_id(), 'ymbeseo_seen_about_version', true ) === YMBESEO_VERSION;
	}

	/**
	 * Notify about the default tagline if the user hasn't changed it
	 */
	public function tagline_notice() {
		if ( current_user_can( 'manage_options' ) && $this->has_default_tagline() && ! $this->seen_tagline_notice() ) {

			// Only add the notice on GET requests and not in the customizer to prevent faulty return url.
			if ( 'GET' !== filter_input( INPUT_SERVER, 'REQUEST_METHOD' ) || is_customize_preview() ) {
				return;
			}

			$current_url = ( is_ssl() ? 'https://' : 'http://' );
			$current_url .= sanitize_text_field( $_SERVER['SERVER_NAME'] ) . sanitize_text_field( $_SERVER['REQUEST_URI'] );
			$customize_url = add_query_arg( array(
				'url' => urlencode( $current_url ),
			), wp_customize_url() );

			$info_message = sprintf(
				__( 'You still have the default WordPress tagline, even an empty one is probably better. %1$sYou can fix this in the customizer%2$s.', 'ymbeseo' ),
				'<a href="' . esc_attr( $customize_url ) . '">',
				'</a>'
			);

			$notification_options = array(
				'type'  => 'error',
				'id'    => 'ymbeseo-dismiss-tagline-notice',
				'nonce' => wp_create_nonce( 'ymbeseo-dismiss-tagline-notice' ),
			);

			Yoast_Notification_Center::get()->add_notification( new Yoast_Notification( $info_message, $notification_options ) );
		}
	}

	/**
	 * Returns whether or not the site has the default tagline
	 *
	 * @return bool
	 */
	public function has_default_tagline() {
		return __( 'Just another WordPress site' ) === get_bloginfo( 'description' );
	}

	/**
	 * Returns whether or not the user has seen the tagline notice
	 *
	 * @return bool
	 */
	public function seen_tagline_notice() {
		return 'seen' === get_user_meta( get_current_user_id(), 'ymbeseo_seen_tagline_notice', true );
	}

	/**
	 * Shows a notice to the user if they have Google Analytics for WordPress 5.4.3 installed because it causes an error
	 * on the google search console page.
	 */
	public function ga_compatibility_notice() {
		if ( defined( 'GAWP_VERSION' ) && '5.4.3' === GAWP_VERSION ) {

			$info_message = sprintf(
				/* translators: %1$s expands to YMBE SEO, %2$s expands to 5.4.3, %3$s expands to Google Analytics by Yoast */
				__( '%1$s detected you are using version %2$s of %3$s, please update to the latest version to prevent compatibility issues.', 'ymbeseo' ),
				'YMBE SEO',
				'5.4.3',
				'Google Analytics by Yoast'
			);

			$notification_options = array(
				'type' => 'error',
			);

			Yoast_Notification_Center::get()->add_notification( new Yoast_Notification( $info_message, $notification_options ) );
		}
	}

	/**
	 * Helper to verify if the user is currently visiting one of our admin pages.
	 *
	 * @return bool
	 */
	private function on_ymbeseo_admin_page() {
		return 'admin.php' === $this->pagenow && strpos( filter_input( INPUT_GET, 'page' ), 'ymbeseo' ) === 0;
	}

	/**
	 * Determine whether we should load the meta box class and if so, load it.
	 */
	private function load_meta_boxes() {
		/**
		 * Filter: 'ymbeseo_always_register_metaboxes_on_admin' - Allow developers to change whether
		 * the YMBESEO metaboxes are only registered on the typical pages (lean loading) or always
		 * registered when in admin.
		 *
		 * @api bool Whether to always register the metaboxes or not. Defaults to false.
		 */
		if ( in_array( $this->pagenow, array(
				'edit.php',
				'post.php',
				'post-new.php',
			) ) || apply_filters( 'ymbeseo_always_register_metaboxes_on_admin', false )
		) {
			$GLOBALS['ymbeseo_metabox'] = new YMBESEO_Metabox;
			if ( $this->options['opengraph'] === true || $this->options['twitter'] === true || $this->options['googleplus'] === true ) {
				new YMBESEO_Social_Admin;
			}
		}
	}

	/**
	 * Determine if we should load our taxonomy edit class and if so, load it.
	 */
	private function load_taxonomy_class() {
		if ( 'edit-tags.php' === $this->pagenow ) {
			new YMBESEO_Taxonomy;
		}
	}

	/**
	 * Determine if we should load our admin pages class and if so, load it.
	 *
	 * Loads admin page class for all admin pages starting with `ymbeseo_`.
	 */
	private function load_admin_user_class() {
		if ( in_array( $this->pagenow, array( 'user-edit.php', 'profile.php' ) ) && current_user_can( 'edit_users' ) ) {
			new YMBESEO_Admin_User_Profile;
		}
	}

	/**
	 * Determine if we should load our admin pages class and if so, load it.
	 *
	 * Loads admin page class for all admin pages starting with `ymbeseo_`.
	 */
	private function load_admin_page_class() {

		if ( $this->on_ymbeseo_admin_page() ) {
			// For backwards compatabilty, this still needs a global, for now...
			$GLOBALS['ymbeseo_admin_pages'] = new YMBESEO_Admin_Pages;
			$this->register_i18n_promo_class();
		}
	}

	/**
	 * Register the promotion class for our GlotPress instance
	 *
	 * @link https://github.com/Yoast/i18n-module
	 */
	private function register_i18n_promo_class() {
		new yoast_i18n(
			array(
				'textdomain'     => 'ymbeseo',
				'project_slug'   => 'ymbeseo',
				'plugin_name'    => 'YMBE SEO',
				'hook'           => 'ymbeseo_admin_footer',
				'glotpress_url'  => 'https://translate.yoast.com/',
				'glotpress_name' => 'Yoast Translate',
				'glotpress_logo' => 'https://cdn.yoast.com/wp-content/uploads/i18n-images/Yoast_Translate.svg',
				'register_url'   => 'https://translate.yoast.com/projects#utm_source=plugin&utm_medium=promo-box&utm_campaign=ymbeseo-i18n-promo',
			)
		);
	}

	/**
	 * See if we should start our XML Sitemaps Admin class
	 */
	private function load_xml_sitemaps_admin() {
		if ( $this->options['enablexmlsitemap'] === true ) {
			new YMBESEO_Sitemaps_Admin;
		}
	}
}
