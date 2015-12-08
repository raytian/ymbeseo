<?php
/**
 * @package YMBESEO\Main
 */

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * @internal Nobody should be able to overrule the real version number as this can cause serious issues
 * with the options, so no if ( ! defined() )
 */
define( 'YMBESEO_VERSION', '2.3.5' );

if ( ! defined( 'YMBESEO_PATH' ) ) {
	define( 'YMBESEO_PATH', plugin_dir_path( YMBESEO_FILE ) );
}

if ( ! defined( 'YMBESEO_BASENAME' ) ) {
	define( 'YMBESEO_BASENAME', plugin_basename( YMBESEO_FILE ) );
}

if ( ! defined( 'YMBESEO_CSSJS_SUFFIX' ) ) {
	define( 'YMBESEO_CSSJS_SUFFIX', ( ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min' ) );
}


/* ***************************** CLASS AUTOLOADING *************************** */

/**
 * Auto load our class files
 *
 * @param string $class Class name.
 *
 * @return void
 */
function ymbeseo_auto_load( $class ) {
	static $classes = null;

	if ( $classes === null ) {
		$classes = array(
			'wp_list_table'                      => ABSPATH . 'wp-admin/includes/class-wp-list-table.php',
			'walker_category'                    => ABSPATH . 'wp-includes/category-template.php',
			'pclzip'                             => ABSPATH . 'wp-admin/includes/class-pclzip.php',
		);
	}

	$cn = strtolower( $class );

	if ( ! class_exists( $class ) && isset( $classes[ $cn ] ) ) {
		require_once( $classes[ $cn ] );
	}
}

if ( file_exists( YMBESEO_PATH . '/vendor/autoload_52.php' ) ) {
	require YMBESEO_PATH . '/vendor/autoload_52.php';
}
elseif ( ! class_exists( 'YMBESEO_Options' ) ) { // Still checking since might be site-level autoload R.
	add_action( 'admin_init', 'so_ymbeseo_missing_autoload', 1 );
	return;
}

if ( function_exists( 'spl_autoload_register' ) ) {
	spl_autoload_register( 'ymbeseo_auto_load' );
}


/* ***************************** PLUGIN (DE-)ACTIVATION *************************** */

/**
 * Run single site / network-wide activation of the plugin.
 *
 * @param bool $networkwide Whether the plugin is being activated network-wide.
 */
function ymbeseo_activate( $networkwide = false ) {
	if ( ! is_multisite() || ! $networkwide ) {
		_ymbeseo_activate();
	}
	else {
		/* Multi-site network activation - activate the plugin for all blogs */
		ymbeseo_network_activate_deactivate( true );
	}
}

/**
 * Run single site / network-wide de-activation of the plugin.
 *
 * @param bool $networkwide Whether the plugin is being de-activated network-wide.
 */
function ymbeseo_deactivate( $networkwide = false ) {
	if ( ! is_multisite() || ! $networkwide ) {
		_ymbeseo_deactivate();
	}
	else {
		/* Multi-site network activation - de-activate the plugin for all blogs */
		ymbeseo_network_activate_deactivate( false );
	}
}

/**
 * Run network-wide (de-)activation of the plugin
 *
 * @param bool $activate True for plugin activation, false for de-activation.
 */
function ymbeseo_network_activate_deactivate( $activate = true ) {
	global $wpdb;

	$original_blog_id = get_current_blog_id(); // Alternatively use: $wpdb->blogid.
	$all_blogs        = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

	if ( is_array( $all_blogs ) && $all_blogs !== array() ) {
		foreach ( $all_blogs as $blog_id ) {
			switch_to_blog( $blog_id );

			if ( $activate === true ) {
				_ymbeseo_activate();
			}
			else {
				_ymbeseo_deactivate();
			}
		}
		// Restore back to original blog.
		switch_to_blog( $original_blog_id );
	}
}

/**
 * Runs on activation of the plugin.
 */
function _ymbeseo_activate() {
	require_once( YMBESEO_PATH . 'inc/ymbeseo-functions.php' );

	ymbeseo_load_textdomain(); // Make sure we have our translations available for the defaults.
	YMBESEO_Options::get_instance();
	if ( ! is_multisite() ) {
		YMBESEO_Options::initialize();
	}
	else {
		YMBESEO_Options::maybe_set_multisite_defaults( true );
	}
	YMBESEO_Options::ensure_options_exist();

	add_action( 'shutdown', 'flush_rewrite_rules' );

	ymbeseo_add_capabilities();

	// Clear cache so the changes are obvious.
	YMBESEO_Utils::clear_cache();

	do_action( 'ymbeseo_activate' );
}

/**
 * On deactivation, flush the rewrite rules so XML sitemaps stop working.
 */
function _ymbeseo_deactivate() {
	require_once( YMBESEO_PATH . 'inc/ymbeseo-functions.php' );

	add_action( 'shutdown', 'flush_rewrite_rules' );

	ymbeseo_remove_capabilities();

	// Clear cache so the changes are obvious.
	YMBESEO_Utils::clear_cache();

	do_action( 'ymbeseo_deactivate' );
}

/**
 * Run ymbeseo activation routine on creation / activation of a multisite blog if YMBESEO is activated
 * network-wide.
 *
 * Will only be called by multisite actions.
 *
 * @internal Unfortunately will fail if the plugin is in the must-use directory
 * @see      https://core.trac.wordpress.org/ticket/24205
 *
 * @param int $blog_id
 */
function ymbeseo_on_activate_blog( $blog_id ) {
	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}

	if ( is_plugin_active_for_network( plugin_basename( YMBESEO_FILE ) ) ) {
		switch_to_blog( $blog_id );
		ymbeseo_activate( false );
		restore_current_blog();
	}
}


/* ***************************** PLUGIN LOADING *************************** */

/**
 * Load translations
 */
function ymbeseo_load_textdomain() {
	$ymbeseo_path = str_replace( '\\', '/', YMBESEO_PATH );
	$mu_path    = str_replace( '\\', '/', WPMU_PLUGIN_DIR );

	if ( false !== stripos( $ymbeseo_path, $mu_path ) ) {
		load_muplugin_textdomain( 'ymbeseo', dirname( YMBESEO_BASENAME ) . '/languages/' );
	}
	else {
		load_plugin_textdomain( 'ymbeseo', false, dirname( YMBESEO_BASENAME ) . '/languages/' );
	}
}

add_action( 'init', 'ymbeseo_load_textdomain', 1 );


/**
 * On plugins_loaded: load the minimum amount of essential files for this plugin
 */
function ymbeseo_init() {
	require_once( YMBESEO_PATH . 'inc/ymbeseo-functions.php' );

	// Make sure our option and meta value validation routines and default values are always registered and available.
	YMBESEO_Options::get_instance();
	YMBESEO_Meta::init();

	$options = YMBESEO_Options::get_all();
	if ( version_compare( $options['version'], YMBESEO_VERSION, '<' ) ) {
		new YMBESEO_Upgrade();
		// Get a cleaned up version of the $options.
		$options = YMBESEO_Options::get_all();
	}

	if ( $options['stripcategorybase'] === true ) {
		$GLOBALS['ymbeseo_rewrite'] = new YMBESEO_Rewrite;
	}

	if ( $options['enablexmlsitemap'] === true ) {
		$GLOBALS['ymbeseo_sitemaps'] = new YMBESEO_Sitemaps;
	}

	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
		require_once( YMBESEO_PATH . 'inc/ymbeseo-non-ajax-functions.php' );
	}

	// Init it here because the filter must be present on the frontend as well or it won't work in the customizer.
	new YMBESEO_Customizer();
}

/**
 * Used to load the required files on the plugins_loaded hook, instead of immediately.
 */
function ymbeseo_frontend_init() {
	add_action( 'init', 'initialize_ymbeseo_front' );

	$options = YMBESEO_Options::get_all();
	if ( $options['breadcrumbs-enable'] === true ) {
		/**
		 * If breadcrumbs are active (which they supposedly are if the users has enabled this settings,
		 * there's no reason to have bbPress breadcrumbs as well.
		 *
		 * @internal The class itself is only loaded when the template tag is encountered via
		 * the template tag function in the ymbeseo-functions.php file
		 */
		add_filter( 'bbp_get_breadcrumb', '__return_false' );
	}

	add_action( 'template_redirect', 'ymbeseo_frontend_head_init', 999 );
}

/**
 * Instantiate the different social classes on the frontend
 */
function ymbeseo_frontend_head_init() {
	$options = YMBESEO_Options::get_all();
	if ( $options['twitter'] === true ) {
		add_action( 'ymbeseo_head', array( 'YMBESEO_Twitter', 'get_instance' ), 40 );
	}

	if ( $options['opengraph'] === true ) {
		$GLOBALS['ymbeseo_og'] = new YMBESEO_OpenGraph;
	}

	if ( $options['googleplus'] === true && is_singular() ) {
		add_action( 'ymbeseo_head', array( 'YMBESEO_GooglePlus', 'get_instance' ), 35 );
	}
}

/**
 * Used to load the required files on the plugins_loaded hook, instead of immediately.
 */
function ymbeseo_admin_init() {
	new YMBESEO_Admin_Init();
}


/* ***************************** BOOTSTRAP / HOOK INTO WP *************************** */
$spl_autoload_exists = function_exists( 'spl_autoload_register' );
$filter_exists       = function_exists( 'filter_input' );

if ( ! $spl_autoload_exists ) {
	add_action( 'admin_init', 'so_ymbeseo_missing_spl', 1 );
}

if ( ! $filter_exists ) {
	add_action( 'admin_init', 'so_ymbeseo_missing_filter', 1 );
}

if ( ( ! defined( 'WP_INSTALLING' ) || WP_INSTALLING === false ) && ( $spl_autoload_exists && $filter_exists ) ) {
	add_action( 'plugins_loaded', 'ymbeseo_init', 14 );

	if ( is_admin() ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			require_once( YMBESEO_PATH . 'admin/ajax.php' );

			// Crawl Issue Manager AJAX hooks.
			new YMBESEO_GSC_Ajax;

			// Plugin conflict ajax hooks.
			new Yoast_Plugin_Conflict_Ajax();

		}
		else {
			add_action( 'plugins_loaded', 'ymbeseo_admin_init', 15 );
		}
	}
	else {
		add_action( 'plugins_loaded', 'ymbeseo_frontend_init', 15 );
	}

	add_action( 'admin_init', 'load_yoast_notifications' );
}

// Activation and deactivation hook.
register_activation_hook( YMBESEO_FILE, 'ymbeseo_activate' );
register_activation_hook( YMBESEO_FILE, array( 'YMBESEO_Plugin_Conflict', 'hook_check_for_plugin_conflicts' ) );
register_deactivation_hook( YMBESEO_FILE, 'ymbeseo_deactivate' );
add_action( 'wpmu_new_blog', 'ymbeseo_on_activate_blog' );
add_action( 'activate_blog', 'ymbeseo_on_activate_blog' );

/**
 * Wraps for notifications center class.
 */
function load_yoast_notifications() {
	// Init Yoast_Notification_Center class.
	Yoast_Notification_Center::get();
}


/**
 * Throw an error if the PHP SPL extension is disabled (prevent white screens) and self-deactivate plugin
 *
 * @since 1.5.4
 *
 * @return void
 */
function so_ymbeseo_missing_spl() {
	if ( is_admin() ) {
		add_action( 'admin_notices', 'so_ymbeseo_missing_spl_notice' );

		so_ymbeseo_self_deactivate();
	}
}

/**
 * Returns the notice in case of missing spl extension
 */
function so_ymbeseo_missing_spl_notice() {
	$message = esc_html__( 'The Standard PHP Library (SPL) extension seem to be unavailable. Please ask your web host to enable it.', 'ymbeseo' );
	so_ymbeseo_activation_failed_notice( $message );
}

/**
 * Throw an error if the Composer autoload is missing and self-deactivate plugin
 *
 * @return void
 */
function so_ymbeseo_missing_autoload() {
	if ( is_admin() ) {
		add_action( 'admin_notices', 'so_ymbeseo_missing_autoload_notice' );

		so_ymbeseo_self_deactivate();
	}
}

/**
 * Returns the notice in case of missing Composer autoload
 */
function so_ymbeseo_missing_autoload_notice() {
	/* translators: %1$s expands to Yoast Minus Bloat Equals SEO, %2$s / %3$s: links to the installation manual in the Readme for the Yoast Minus Bloat Equals SEO code repository on GitHub */
	$message = esc_html__( 'The %1$s plugin installation is incomplete. Please refer to %2$sinstallation instructions%3$s.', 'ymbeseo' );
	$message = sprintf( $message, 'Yoast Minus Bloat Equals SEO', '<a href="https://github.com/Yoast/ymbeseo#installation">', '</a>' );
	so_ymbeseo_activation_failed_notice( $message );
}

/**
 * Throw an error if the filter extension is disabled (prevent white screens) and self-deactivate plugin
 *
 * @since 2.0
 *
 * @return void
 */
function so_ymbeseo_missing_filter() {
	if ( is_admin() ) {
		add_action( 'admin_notices', 'so_ymbeseo_missing_filter_notice' );

		so_ymbeseo_self_deactivate();
	}
}

/**
 * Returns the notice in case of missing filter extension
 */
function so_ymbeseo_missing_filter_notice() {
	$message = esc_html__( 'The filter extension seem to be unavailable. Please ask your web host to enable it.', 'ymbeseo' );
	so_ymbeseo_activation_failed_notice( $message );
}

/**
 * Echo's the Activation failed notice with any given message.
 *
 * @param string $message
 */
function so_ymbeseo_activation_failed_notice( $message ) {
	echo '<div class="error"><p>' . __( 'Activation failed:', 'ymbeseo' ) . ' ' . $message . '</p></div>';
}

/**
 * The method will deactivate the plugin, but only once, done by the static $is_deactivated
 */
function so_ymbeseo_self_deactivate() {
	static $is_deactivated;

	if ( $is_deactivated === null ) {
		$is_deactivated = true;
		deactivate_plugins( plugin_basename( YMBESEO_FILE ) );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}
