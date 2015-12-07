<?php
/**
 * @package YMBESEO\Admin
 */

/**
 * Class YMBESEO_Admin_Pages
 *
 * Class with functionality for the Yoast SEO admin pages.
 */
class YMBESEO_Admin_Pages {

	/**
	 * @var string $currentoption The option in use for the current admin page.
	 */
	public $currentoption = 'wpseo';

	/**
	 * Class constructor, which basically only hooks the init function on the init hook
	 */
	function __construct() {
		add_action( 'init', array( $this, 'init' ), 20 );
	}

	/**
	 * Make sure the needed scripts are loaded for admin pages
	 */
	function init() {
		if ( filter_input( INPUT_GET, 'YMBESEO_reset_defaults' ) && wp_verify_nonce( filter_input( INPUT_GET, 'nonce' ), 'YMBESEO_reset_defaults' ) && current_user_can( 'manage_options' ) ) {
			YMBESEO_Options::reset();
			wp_redirect( admin_url( 'admin.php?page=YMBESEO_dashboard' ) );
		}

		if ( YMBESEO_Utils::grant_access() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'config_page_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'config_page_styles' ) );
		}
	}

	/**
	 * Loads the required styles for the config page.
	 */
	function config_page_styles() {
		wp_enqueue_style( 'dashboard' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style( 'global' );
		wp_enqueue_style( 'wp-admin' );
		wp_enqueue_style( 'yoast-admin-css', plugins_url( 'css/yst_plugin_tools' . YMBESEO_CSSJS_SUFFIX . '.css', YMBESEO_FILE ), array(), YMBESEO_VERSION );

		if ( is_rtl() ) {
			wp_enqueue_style( 'wpseo-rtl', plugins_url( 'css/wpseo-rtl' . YMBESEO_CSSJS_SUFFIX . '.css', YMBESEO_FILE ), array(), YMBESEO_VERSION );
		}
	}

	/**
	 * Loads the required scripts for the config page.
	 */
	function config_page_scripts() {
		wp_enqueue_script( 'wpseo-admin-script', plugins_url( 'js/wp-seo-admin' . YMBESEO_CSSJS_SUFFIX . '.js', YMBESEO_FILE ), array(
			'jquery',
			'jquery-ui-core',
		), YMBESEO_VERSION, true );
		wp_localize_script( 'wpseo-admin-script', 'wpseoAdminL10n', $this->localize_admin_script() );
		wp_enqueue_script( 'dashboard' );
		wp_enqueue_script( 'thickbox' );

		$page = filter_input( INPUT_GET, 'page' );
		$tool = filter_input( INPUT_GET, 'tool' );

		if ( in_array( $page, array( 'YMBESEO_social', 'YMBESEO_dashboard' ) ) ) {
			wp_enqueue_media();
			wp_enqueue_script( 'wpseo-admin-media', plugins_url( 'js/wp-seo-admin-media' . YMBESEO_CSSJS_SUFFIX . '.js', YMBESEO_FILE ), array(
				'jquery',
				'jquery-ui-core',
			), YMBESEO_VERSION, true );
			wp_localize_script( 'wpseo-admin-media', 'wpseoMediaL10n', $this->localize_media_script() );
		}

		if ( 'YMBESEO_tools' === $page && 'bulk-editor' === $tool ) {
			wp_enqueue_script( 'wpseo-bulk-editor', plugins_url( 'js/wp-seo-bulk-editor' . YMBESEO_CSSJS_SUFFIX . '.js', YMBESEO_FILE ), array( 'jquery' ), YMBESEO_VERSION, true );
		}

		if ( 'YMBESEO_tools' === $page && 'import-export' === $tool ) {
			wp_enqueue_script( 'wpseo-export', plugins_url( 'js/wp-seo-export' . YMBESEO_CSSJS_SUFFIX . '.js', YMBESEO_FILE ), array( 'jquery' ), YMBESEO_VERSION, true );
		}
	}

	/**
	 * Pass some variables to js for upload module.
	 *
	 * @return  array
	 */
	public function localize_media_script() {
		return array(
			'choose_image' => __( 'Use Image', 'ymbeseo' ),
		);
	}

	/**
	 * Pass some variables to js for the admin JS module.
	 *
	 * %s is replaced with <code>%s</code> and replaced again in the javascript with the actual variable.
	 *
	 * @return  array
	 */
	public function localize_admin_script() {
		return array(
			/* translators: %s: '%%term_title%%' variable used in titles and meta's template that's not compatible with the given template */
			'variable_warning' => sprintf( __( 'Warning: the variable %s cannot be used in this template.', 'ymbeseo' ), '<code>%s</code>' ) . ' ' . __( 'See the help tab for more info.', 'ymbeseo' ),
		);
	}
} /* End of class */
