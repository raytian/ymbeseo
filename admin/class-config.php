<?php
/**
 * @package YMBESEO\Admin
 */

/**
 * Class YMBESEO_Admin_Pages
 *
 * Class with functionality for the YMBE SEO admin pages.
 */
class YMBESEO_Admin_Pages {

	/**
	 * @var string $currentoption The option in use for the current admin page.
	 */
	public $currentoption = 'ymbeseo';

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
		if ( filter_input( INPUT_GET, 'ymbeseo_reset_defaults' ) && wp_verify_nonce( filter_input( INPUT_GET, 'nonce' ), 'ymbeseo_reset_defaults' ) && current_user_can( 'manage_options' ) ) {
			YMBESEO_Options::reset();
			wp_redirect( admin_url( 'admin.php?page=ymbeseo_dashboard' ) );
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
			wp_enqueue_style( 'ymbeseo-rtl', plugins_url( 'css/ymbeseo-rtl' . YMBESEO_CSSJS_SUFFIX . '.css', YMBESEO_FILE ), array(), YMBESEO_VERSION );
		}
	}

	/**
	 * Loads the required scripts for the config page.
	 */
	function config_page_scripts() {
		wp_enqueue_script( 'ymbeseo-admin-script', plugins_url( 'js/ymbeseo-admin' . YMBESEO_CSSJS_SUFFIX . '.js', YMBESEO_FILE ), array(
			'jquery',
			'jquery-ui-core',
		), YMBESEO_VERSION, true );
		wp_localize_script( 'ymbeseo-admin-script', 'ymbeseoAdminL10n', $this->localize_admin_script() );
		wp_enqueue_script( 'dashboard' );
		wp_enqueue_script( 'thickbox' );

		$page = filter_input( INPUT_GET, 'page' );
		$tool = filter_input( INPUT_GET, 'tool' );

		if ( in_array( $page, array( 'ymbeseo_social', 'ymbeseo_dashboard' ) ) ) {
			wp_enqueue_media();
			wp_enqueue_script( 'ymbeseo-admin-media', plugins_url( 'js/ymbeseo-admin-media' . YMBESEO_CSSJS_SUFFIX . '.js', YMBESEO_FILE ), array(
				'jquery',
				'jquery-ui-core',
			), YMBESEO_VERSION, true );
			wp_localize_script( 'ymbeseo-admin-media', 'ymbeseoMediaL10n', $this->localize_media_script() );
		}

		if ( 'ymbeseo_tools' === $page && 'bulk-editor' === $tool ) {
			wp_enqueue_script( 'ymbeseo-bulk-editor', plugins_url( 'js/ymbeseo-bulk-editor' . YMBESEO_CSSJS_SUFFIX . '.js', YMBESEO_FILE ), array( 'jquery' ), YMBESEO_VERSION, true );
		}

		if ( 'ymbeseo_tools' === $page && 'import-export' === $tool ) {
			wp_enqueue_script( 'ymbeseo-export', plugins_url( 'js/ymbeseo-export' . YMBESEO_CSSJS_SUFFIX . '.js', YMBESEO_FILE ), array( 'jquery' ), YMBESEO_VERSION, true );
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
}
