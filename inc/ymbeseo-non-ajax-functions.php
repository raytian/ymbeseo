<?php
/**
 * @package YMBESEO\Internals
 */

if ( ! defined( 'YMBESEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 * Test whether force rewrite should be enabled or not.
 */
function ymbeseo_title_test() {
	$options = get_option( 'ymbeseo_titles' );

	$options['forcerewritetitle'] = false;
	$options['title_test']        = 1;
	update_option( 'ymbeseo_titles', $options );

	// Setting title_test to > 0 forces the plugin to output the title below through a filter in class-frontend.php.
	$expected_title = 'This is a Yoast Test Title';

	YMBESEO_Utils::clear_cache();


	$args = array(
		'user-agent' => sprintf( 'WordPress/%1$s; %2$s - Yoast', $GLOBALS['wp_version'], get_site_url() ),
	);
	$resp = wp_remote_get( get_bloginfo( 'url' ), $args );

	if ( ( $resp && ! is_wp_error( $resp ) ) && ( 200 == $resp['response']['code'] && isset( $resp['body'] ) ) ) {
		$res = preg_match( '`<title>([^<]+)</title>`im', $resp['body'], $matches );

		if ( $res && strcmp( $matches[1], $expected_title ) !== 0 ) {
			$options['forcerewritetitle'] = true;

			$resp = wp_remote_get( get_bloginfo( 'url' ), $args );
			$res  = false;
			if ( ( $resp && ! is_wp_error( $resp ) ) && ( 200 == $resp['response']['code'] && isset( $resp['body'] ) ) ) {
				$res = preg_match( '`/<title>([^>]+)</title>`im', $resp['body'], $matches );
			}
		}

		if ( ! $res || $matches[1] != $expected_title ) {
			$options['forcerewritetitle'] = false;
		}
	}
	else {
		// If that dies, let's make sure the titles are correct and force the output.
		$options['forcerewritetitle'] = true;
	}

	$options['title_test'] = 0;
	update_option( 'ymbeseo_titles', $options );
}

// Commented out? add_filter( 'switch_theme', 'ymbeseo_title_test', 0 ); R.
/**
 * Test whether the active theme contains a <meta> description tag.
 *
 * @since 1.4.14 Moved from dashboard.php and adjusted - see changelog
 *
 * @return void
 */
function ymbeseo_description_test() {
	$options = get_option( 'ymbeseo' );

	// Reset any related options - dirty way of getting the default to make sure it works on activation.
	$options['theme_has_description']   = YMBESEO_Option_Wpseo::$desc_defaults['theme_has_description'];
	$options['theme_description_found'] = YMBESEO_Option_Wpseo::$desc_defaults['theme_description_found'];

	/**
	 * @internal Should this be reset too ? Best to do so as test is done on re-activate and switch_theme
	 * as well and new warning would be warranted then. Only might give irritation on theme upgrade.
	 */
	$options['ignore_meta_description_warning'] = YMBESEO_Option_Wpseo::$desc_defaults['ignore_meta_description_warning'];

	$file = false;
	if ( file_exists( get_stylesheet_directory() . '/header.php' ) ) {
		// Theme or child theme.
		$file = get_stylesheet_directory() . '/header.php';
	}
	elseif ( file_exists( get_template_directory() . '/header.php' ) ) {
		// Parent theme in case of a child theme.
		$file = get_template_directory() . '/header.php';
	}

	if ( is_string( $file ) && $file !== '' ) {
		$header_file = file_get_contents( $file );
		$issue       = preg_match_all( '#<\s*meta\s*(name|content)\s*=\s*("|\')(.*)("|\')\s*(name|content)\s*=\s*("|\')(.*)("|\')(\s+)?/?>#i', $header_file, $matches, PREG_SET_ORDER );
		if ( $issue === false || $issue === 0 ) {
			$options['theme_has_description'] = false;
		}
		else {
			foreach ( $matches as $meta ) {
				if ( ( strtolower( $meta[1] ) == 'name' && strtolower( $meta[3] ) == 'description' ) || ( strtolower( $meta[5] ) == 'name' && strtolower( $meta[7] ) == 'description' ) ) {
					$options['theme_description_found']         = $meta[0];
					$options['ignore_meta_description_warning'] = false;
					break; // No need to run through the rest of the meta's.
				}
			}
			if ( $options['theme_description_found'] !== '' ) {
				$options['theme_has_description'] = true;
			}
			else {
				$options['theme_has_description'] = false;
			}
		}
	}
	update_option( 'ymbeseo', $options );
}

add_filter( 'after_switch_theme', 'ymbeseo_description_test', 0 );

if ( version_compare( $GLOBALS['wp_version'], '3.6.99', '>' ) ) {
	// Use the new and *sigh* adjusted action hook WP 3.7+.
	add_action( 'upgrader_process_complete', 'ymbeseo_upgrader_process_complete', 10, 2 );
}
elseif ( version_compare( $GLOBALS['wp_version'], '3.5.99', '>' ) ) {
	// Use the new action hook WP 3.6+.
	add_action( 'upgrader_process_complete', 'ymbeseo_upgrader_process_complete', 10, 3 );
}
else {
	// Abuse filters to do our action.
	add_filter( 'update_theme_complete_actions', 'ymbeseo_update_theme_complete_actions', 10, 2 );
	add_filter( 'update_bulk_theme_complete_actions', 'ymbeseo_update_theme_complete_actions', 10, 2 );
}


/**
 * Check if the current theme was updated and if so, test the updated theme
 * for the title and meta description tag
 *
 * @since    1.4.14
 *
 * @param   object $upgrader_object
 * @param   array  $context_array
 * @param   mixed  $themes
 *
 * @return  void
 */
function ymbeseo_upgrader_process_complete( $upgrader_object, $context_array, $themes = null ) {
	$options = get_option( 'ymbeseo' );

	// Break if admin_notice already in place.
	if ( ( ( isset( $options['theme_has_description'] ) && $options['theme_has_description'] === true ) || $options['theme_description_found'] !== '' ) && $options['ignore_meta_description_warning'] !== true ) {
		return;
	}
	// Break if this is not a theme update, not interested in installs as after_switch_theme would still be called.
	if ( ! isset( $context_array['type'] ) || $context_array['type'] !== 'theme' || ! isset( $context_array['action'] ) || $context_array['action'] !== 'update' ) {
		return;
	}

	$theme = get_stylesheet();
	if ( ! isset( $themes ) ) {
		// WP 3.7+.
		$themes = array();
		if ( isset( $context_array['themes'] ) && $context_array['themes'] !== array() ) {
			$themes = $context_array['themes'];
		}
		elseif ( isset( $context_array['theme'] ) && $context_array['theme'] !== '' ) {
			$themes = $context_array['theme'];
		}
	}

	if ( ( isset( $context_array['bulk'] ) && $context_array['bulk'] === true ) && ( is_array( $themes ) && count( $themes ) > 0 ) ) {

		if ( in_array( $theme, $themes ) ) {
			// Commented out? ymbeseo_title_test(); R.
			ymbeseo_description_test();
		}
	}
	elseif ( is_string( $themes ) && $themes === $theme ) {
		// Commented out? ymbeseo_title_test(); R.
		ymbeseo_description_test();
	}

	return;
}

/**
 * Abuse a filter to check if the current theme was updated and if so, test the updated theme
 * for the title and meta description tag
 *
 * @since 1.4.14
 *
 * @param   array $update_actions
 * @param   mixed $updated_theme
 *
 * @return  array  $update_actions    Unchanged array
 */
function ymbeseo_update_theme_complete_actions( $update_actions, $updated_theme ) {
	$options = get_option( 'ymbeseo' );

	// Break if admin_notice already in place.
	if ( ( ( isset( $options['theme_has_description'] ) && $options['theme_has_description'] === true ) || $options['theme_description_found'] !== '' ) && $options['ignore_meta_description_warning'] !== true ) {
		return $update_actions;
	}

	$theme = get_stylesheet();
	if ( is_object( $updated_theme ) ) {
		/*
		Bulk update and $updated_theme only contains info on which theme was last in the list
		   of updated themes, so go & test
		*/

		// Commented out? ymbeseo_title_test(); R.
		ymbeseo_description_test();
	}
	elseif ( $updated_theme === $theme ) {
		/*
		Single theme update for the active theme
		*/

		// Commented out? ymbeseo_title_test(); R.
		ymbeseo_description_test();
	}

	return $update_actions;
}


/**
 * Adds an SEO admin bar menu with several options. If the current user is an admin he can also go straight to several settings menu's from here.
 */
function ymbeseo_admin_bar_menu() {
	// If the current user can't write posts, this is all of no use, so let's not output an admin menu.
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	global $wp_admin_bar, $post;

	$focuskw = '';
	$score   = '';
	$seo_url = get_admin_url( null, 'admin.php?page=ymbeseo_dashboard' );

	if ( ( is_singular() || ( is_admin() && in_array( $GLOBALS['pagenow'], array(
					'post.php',
					'post-new.php',
				), true ) ) ) && isset( $post ) && is_object( $post ) && apply_filters( 'ymbeseo_use_page_analysis', true ) === true
	) {
		$focuskw    = YMBESEO_Meta::get_value( 'focuskw', $post->ID );
		$perc_score = YMBESEO_Meta::get_value( 'linkdex', $post->ID );
		$calc_score = YMBESEO_Utils::calc( $perc_score, '/', 10, true );
		$txtscore   = YMBESEO_Utils::translate_score( $calc_score );
		$title      = YMBESEO_Utils::translate_score( $calc_score, false );
		$score      = '<div title="' . esc_attr( $title ) . '" class="' . esc_attr( 'ymbeseo-score-icon ' . $txtscore . ' ' . $perc_score ) . '"></div>';

		$seo_url = get_edit_post_link( $post->ID );
		if ( $txtscore !== 'na' ) {
			$seo_url .= '#ymbeseo_linkdex';
		}
	}

	$wp_admin_bar->add_menu( array(
		'id'    => 'ymbeseo-menu',
		'title' => __( 'SEO', 'ymbeseo' ) . $score,
		'href'  => $seo_url,
	) );
	$wp_admin_bar->add_menu( array(
		'parent' => 'ymbeseo-menu',
		'id'     => 'ymbeseo-kwresearch',
		'title'  => __( 'Keyword Research', 'ymbeseo' ),
		'#',
	) );
	$wp_admin_bar->add_menu( array(
		'parent' => 'ymbeseo-kwresearch',
		'id'     => 'ymbeseo-adwordsexternal',
		'title'  => __( 'AdWords External', 'ymbeseo' ),
		'href'   => 'http://adwords.google.com/keywordplanner',
		'meta'   => array( 'target' => '_blank' ),
	) );
	$wp_admin_bar->add_menu( array(
		'parent' => 'ymbeseo-kwresearch',
		'id'     => 'ymbeseo-googleinsights',
		'title'  => __( 'Google Insights', 'ymbeseo' ),
		'href'   => 'http://www.google.com/insights/search/#q=' . urlencode( $focuskw ) . '&cmpt=q',
		'meta'   => array( 'target' => '_blank' ),
	) );
	$wp_admin_bar->add_menu( array(
		'parent' => 'ymbeseo-kwresearch',
		'id'     => 'ymbeseo-wordtracker',
		'title'  => __( 'SEO Book', 'ymbeseo' ),
		'href'   => 'http://tools.seobook.com/keyword-tools/seobook/?keyword=' . urlencode( $focuskw ),
		'meta'   => array( 'target' => '_blank' ),
	) );

	if ( ! is_admin() ) {
		$url = YMBESEO_Frontend::get_instance()->canonical( false );

		if ( is_string( $url ) ) {
			$wp_admin_bar->add_menu( array(
				'parent' => 'ymbeseo-menu',
				'id'     => 'ymbeseo-analysis',
				'title'  => __( 'Analyze this page', 'ymbeseo' ),
				'#',
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'ymbeseo-analysis',
				'id'     => 'ymbeseo-inlinks-ose',
				'title'  => __( 'Check Inlinks (OSE)', 'ymbeseo' ),
				'href'   => '//moz.com/researchtools/ose/links?site=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'ymbeseo-analysis',
				'id'     => 'ymbeseo-kwdensity',
				'title'  => __( 'Check Keyword Density', 'ymbeseo' ),
				'href'   => '//www.zippy.co.uk/keyworddensity/index.php?url=' . urlencode( $url ) . '&keyword=' . urlencode( $focuskw ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'ymbeseo-analysis',
				'id'     => 'ymbeseo-cache',
				'title'  => __( 'Check Google Cache', 'ymbeseo' ),
				'href'   => '//webcache.googleusercontent.com/search?strip=1&q=cache:' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'ymbeseo-analysis',
				'id'     => 'ymbeseo-header',
				'title'  => __( 'Check Headers', 'ymbeseo' ),
				'href'   => '//quixapp.com/headers/?r=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'ymbeseo-analysis',
				'id'     => 'ymbeseo-richsnippets',
				'title'  => __( 'Check Rich Snippets', 'ymbeseo' ),
				'href'   => '//www.google.com/webmasters/tools/richsnippets?q=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'ymbeseo-analysis',
				'id'     => 'ymbeseo-facebookdebug',
				'title'  => __( 'Facebook Debugger', 'ymbeseo' ),
				'href'   => '//developers.facebook.com/tools/debug/og/object?q=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'ymbeseo-analysis',
				'id'     => 'ymbeseo-pinterestvalidator',
				'title'  => __( 'Pinterest Rich Pins Validator', 'ymbeseo' ),
				'href'   => '//developers.pinterest.com/rich_pins/validator/?link=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'ymbeseo-analysis',
				'id'     => 'ymbeseo-htmlvalidation',
				'title'  => __( 'HTML Validator', 'ymbeseo' ),
				'href'   => '//validator.w3.org/check?uri=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'ymbeseo-analysis',
				'id'     => 'ymbeseo-cssvalidation',
				'title'  => __( 'CSS Validator', 'ymbeseo' ),
				'href'   => '//jigsaw.w3.org/css-validator/validator?uri=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'ymbeseo-analysis',
				'id'     => 'ymbeseo-pagespeed',
				'title'  => __( 'Google Page Speed Test', 'ymbeseo' ),
				'href'   => '//developers.google.com/speed/pagespeed/insights/?url=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'ymbeseo-analysis',
				'id'     => 'ymbeseo-modernie',
				'title'  => __( 'Modern IE Site Scan', 'ymbeseo' ),
				'href'   => '//www.modern.ie/en-us/report#' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
			$wp_admin_bar->add_menu( array(
				'parent' => 'ymbeseo-analysis',
				'id'     => 'ymbeseo-google-mobile-friendly',
				'title'  => __( 'Mobile-Friendly Test', 'ymbeseo' ),
				'href'   => 'https://www.google.com/webmasters/tools/mobile-friendly/?url=' . urlencode( $url ),
				'meta'   => array( 'target' => '_blank' ),
			) );
		}
	}

	$admin_menu = current_user_can( 'manage_options' );

	if ( ! $admin_menu && is_multisite() ) {
		$options = get_site_option( 'ymbeseo_ms' );
		$admin_menu = ( $options['access'] === 'superadmin' && is_super_admin() );
	}

	// @todo: add links to bulk title and bulk description edit pages.
	if ( $admin_menu ) {
		$wp_admin_bar->add_menu( array(
			'parent' => 'ymbeseo-menu',
			'id'     => 'ymbeseo-settings',
			'title'  => __( 'SEO Settings', 'ymbeseo' ),
		) );
		$wp_admin_bar->add_menu( array(
			'parent' => 'ymbeseo-settings',
			'id'     => 'ymbeseo-general',
			'title'  => __( 'General', 'ymbeseo' ),
			'href'   => admin_url( 'admin.php?page=ymbeseo_dashboard' ),
		) );
		$wp_admin_bar->add_menu( array(
			'parent' => 'ymbeseo-settings',
			'id'     => 'ymbeseo-titles',
			'title'  => __( 'Titles &amp; Metas', 'ymbeseo' ),
			'href'   => admin_url( 'admin.php?page=ymbeseo_titles' ),
		) );
		$wp_admin_bar->add_menu( array(
			'parent' => 'ymbeseo-settings',
			'id'     => 'ymbeseo-social',
			'title'  => __( 'Social', 'ymbeseo' ),
			'href'   => admin_url( 'admin.php?page=ymbeseo_social' ),
		) );
		$wp_admin_bar->add_menu( array(
			'parent' => 'ymbeseo-settings',
			'id'     => 'ymbeseo-xml',
			'title'  => __( 'XML Sitemaps', 'ymbeseo' ),
			'href'   => admin_url( 'admin.php?page=ymbeseo_xml' ),
		) );
		$wp_admin_bar->add_menu( array(
			'parent' => 'ymbeseo-settings',
			'id'     => 'ymbeseo-ymbeseo_advanced',
			'title'  => __( 'Advanced', 'ymbeseo' ),
			'href'   => admin_url( 'admin.php?page=ymbeseo_advanced' ),
		) );
	}
}

add_action( 'admin_bar_menu', 'ymbeseo_admin_bar_menu', 95 );

/**
 * Enqueue a tiny bit of CSS to show so the adminbar shows right.
 */
function ymbeseo_admin_bar_css() {
	if ( is_admin_bar_showing() && is_singular() ) {
		wp_enqueue_style( 'boxes', plugins_url( 'css/adminbar' . YMBESEO_CSSJS_SUFFIX . '.css', YMBESEO_FILE ), array(), YMBESEO_VERSION );
	}
}

add_action( 'wp_enqueue_scripts', 'ymbeseo_admin_bar_css' );

/**
 * Allows editing of the meta fields through weblog editors like Marsedit.
 *
 * @param array $allcaps Capabilities that must all be true to allow action.
 * @param array $cap     Array of capabilities to be checked, unused here.
 * @param array $args    List of arguments for the specific cap to be checked.
 *
 * @return array $allcaps
 */
function allow_custom_field_edits( $allcaps, $cap, $args ) {
	// $args[0] holds the capability.
	// $args[2] holds the post ID.
	// $args[3] holds the custom field.
	// Make sure the request is to edit or add a post meta (this is usually also the second value in $cap,
	// but this is safer to check).
	if ( in_array( $args[0], array( 'edit_post_meta', 'add_post_meta' ) ) ) {
		// Only allow editing rights for users who have the rights to edit this post and make sure
		// the meta value starts with _so_ymbeseo (YMBESEO_Meta::$meta_prefix).
		if ( ( isset( $args[2] ) && current_user_can( 'edit_post', $args[2] ) ) && ( ( isset( $args[3] ) && $args[3] !== '' ) && strpos( $args[3], YMBESEO_Meta::$meta_prefix ) === 0 ) ) {
			$allcaps[ $args[0] ] = true;
		}
	}

	return $allcaps;
}

add_filter( 'user_has_cap', 'allow_custom_field_edits', 0, 3 );

/**
 * Display an import message when robots-meta is active
 *
 * @since 1.5.0
 */
function ymbeseo_robots_meta_message() {
	// Check if robots meta is running.
	if ( ( ! isset( $_GET['page'] ) || 'ymbeseo_import' !== $_GET['page'] ) && is_plugin_active( 'robots-meta/robots-meta.php' ) ) {
		add_action( 'admin_notices', 'ymbeseo_import_robots_meta_notice' );
	}
}

add_action( 'admin_init', 'ymbeseo_robots_meta_message' );

/**
 * Handle deactivation Robots Meta
 *
 * @since 1.5.0
 */
function ymbeseo_disable_robots_meta() {
	if ( isset( $_GET['deactivate_robots_meta'] ) && $_GET['deactivate_robots_meta'] === '1' && is_plugin_active( 'robots-meta/robots-meta.php' ) ) {
		// Deactivate the plugin.
		deactivate_plugins( 'robots-meta/robots-meta.php' );

		// Show notice that robots meta has been deactivated.
		add_action( 'admin_notices', 'ymbeseo_deactivate_robots_meta_notice' );

		// Clean up the referrer url for later use.
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'deactivate_robots_meta' ), sanitize_text_field( $_SERVER['REQUEST_URI'] ) );
		}
	}
}

add_action( 'admin_init', 'ymbeseo_disable_robots_meta' );

/**
 * Handle deactivation AIOSEO
 *
 * @since 1.5.0
 */
function ymbeseo_disable_aioseo() {
	if ( isset( $_GET['deactivate_aioseo'] ) && $_GET['deactivate_aioseo'] === '1' && is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ) {
		// Deactivate AIO.
		deactivate_plugins( 'all-in-one-seo-pack/all_in_one_seo_pack.php' );

		// Show notice that aioseo has been deactivated.
		add_action( 'admin_notices', 'ymbeseo_deactivate_aioseo_notice' );

		// Clean up the referrer url for later use.
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'deactivate_aioseo' ), sanitize_text_field( $_SERVER['REQUEST_URI'] ) );
		}
	}
}

add_action( 'admin_init', 'ymbeseo_disable_aioseo' );

/**
 * Throw a notice to inform the user AIOSEO has been deactivated
 *
 * @since 1.4.8
 */
function ymbeseo_deactivate_aioseo_notice() {
	echo '<div class="updated"><p>', esc_html__( 'All-In-One-SEO has been deactivated', 'ymbeseo' ), '</p></div>';
}

/**
 * Throw a notice to import Robots Meta.
 *
 * @since 1.4.8
 */
function ymbeseo_import_robots_meta_notice() {
	$url = add_query_arg( array( '_wpnonce' => wp_create_nonce( 'ymbeseo-import' ) ), admin_url( 'admin.php?page=ymbeseo_tools&tool=import-export&import=1&importrobotsmeta=1#top#import-other' ) );
	echo '<div class="error"><p>', sprintf( esc_html__( 'The plugin Robots-Meta has been detected. Do you want to %simport its settings%s.', 'ymbeseo' ), sprintf( '<a href="%s">', esc_url( $url ) ), '</a>' ), '</p></div>';
}

/**
 * Throw a notice to inform the user Robots Meta has been deactivated
 *
 * @since 1.4.8
 */
function ymbeseo_deactivate_robots_meta_notice() {
	echo '<div class="updated"><p>', esc_html__( 'Robots-Meta has been deactivated', 'ymbeseo' ), '</p></div>';
}
