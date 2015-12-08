<?php
/**
 * @package    YMBESEO
 * @subpackage Internal
 */

/**
 * This code handles the option upgrades
 */
class YMBESEO_Upgrade {

	/**
	 * Holds the Yoast Minus Bloat Equals SEO options
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->options = YMBESEO_Options::get_all();

		YMBESEO_Options::maybe_set_multisite_defaults( false );

		$this->init();

		if ( version_compare( $this->options['version'], '1.5.0', '<' ) ) {
			$this->upgrade_15( $this->options['version'] );
		}

		if ( version_compare( $this->options['version'], '2.0', '<' ) ) {
			$this->upgrade_20();
		}

		if ( version_compare( $this->options['version'], '2.1', '<' ) ) {
			$this->upgrade_21();
		}

		if ( version_compare( $this->options['version'], '2.2', '<' ) ) {
			$this->upgrade_22();
		}

		if ( version_compare( $this->options['version'], '2.3', '<' ) ) {
			$this->upgrade_23();
		}

		/**
		 * Filter: 'ymbeseo_run_upgrade' - Runs the upgrade hook which are dependent on Yoast Minus Bloat Equals SEO
		 *
		 * @api string - The current version of Yoast Minus Bloat Equals SEO
		 */
		do_action( 'ymbeseo_run_upgrade', $this->options['version'] );

		$this->finish_up();
	}

	/**
	 * Run some functions that run when we first run or when we upgrade Yoast Minus Bloat Equals SEO from < 1.4.13
	 */
	private function init() {
		if ( $this->options['version'] === '' || version_compare( $this->options['version'], '1.4.13', '<' ) ) {
			/* Make sure title_test and description_test functions are available */
			require_once( YMBESEO_PATH . 'inc/ymbeseo-non-ajax-functions.php' );

			// Run description test once theme has loaded.
			add_action( 'init', 'ymbeseo_description_test' );
		}
	}

	/**
	 * Run the Yoast Minus Bloat Equals SEO 1.5 upgrade routine
	 *
	 * @param string $version
	 */
	private function upgrade_15( $version ) {
		// Clean up options and meta.
		YMBESEO_Options::clean_up( null, $version );
		YMBESEO_Meta::clean_up();

		// Add new capabilities on upgrade.
		ymbeseo_add_capabilities();
	}

	/**
	 * Moves options that moved position in YMBESEO 2.0
	 */
	private function upgrade_20() {
		/**
		 * Clean up stray ymbeseo_ms options from the options table, option should only exist in the sitemeta table.
		 * This could have been caused in many version of Yoast Minus Bloat Equals SEO, so deleting it for everything below 2.0
		 */
		delete_option( 'ymbeseo_ms' );

		$this->move_hide_links_options();
		$this->move_pinterest_option();
	}

	/**
	 * Detects if taxonomy terms were split and updates the corresponding taxonomy meta's accordingly.
	 */
	private function upgrade_21() {
		$taxonomies = get_option( 'ymbeseo_taxonomy_meta', array() );

		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy => $tax_metas ) {
				foreach ( $tax_metas as $term_id => $tax_meta ) {
					if ( function_exists( 'wp_get_split_term' ) && $new_term_id = wp_get_split_term( $term_id, $taxonomy ) ) {
						$taxonomies[ $taxonomy ][ $new_term_id ] = $taxonomies[ $taxonomy ][ $term_id ];
						unset( $taxonomies[ $taxonomy ][ $term_id ] );
					}
				}
			}

			update_option( 'ymbeseo_taxonomy_meta', $taxonomies );
		}
	}

	/**
	 * Performs upgrade functions to Yoast Minus Bloat Equals SEO 2.2
	 */
	private function upgrade_22() {
		// Unschedule our tracking.
		wp_clear_scheduled_hook( 'yoast_tracking' );

		// Clear the tracking settings, the seen about setting and the ignore tour setting.
		$options = get_option( 'ymbeseo' );
		unset( $options['tracking_popup_done'], $options['yoast_tracking'], $options['seen_about'], $options['ignore_tour'] );
		update_option( 'ymbeseo', $options );
	}

	/**
	 * Schedules upgrade function to Yoast Minus Bloat Equals SEO 2.3
	 */
	private function upgrade_23() {
		add_action( 'wp', array( $this, 'upgrade_23_query' ), 90 );
		add_action( 'admin_head', array( $this, 'upgrade_23_query' ), 90 );
	}

	/**
	 * Performs upgrade query to Yoast Minus Bloat Equals SEO 2.3
	 */
	public function upgrade_23_query() {
		$wp_query = new WP_Query( 'post_type=any&meta_key=_yoast_ymbeseo_sitemap-include&meta_value=never&order=ASC' );

		if ( ! empty( $wp_query->posts ) ) {
			$options = get_option( 'ymbeseo_xml' );

			$excluded_posts = array();
			if ( $options['excluded-posts'] !== '' ) {
				$excluded_posts = explode( ',', $options['excluded-posts'] );
			}

			foreach ( $wp_query->posts as $post ) {
				if ( ! in_array( $post->ID, $excluded_posts ) ) {
					$excluded_posts[] = $post->ID;
				}
			}

			// Updates the meta value.
			$options['excluded-posts'] = implode( ',', $excluded_posts );

			// Update the option.
			update_option( 'ymbeseo_xml', $options );
		}

		// Remove the meta fields.
		delete_post_meta_by_key( '_yoast_ymbeseo_sitemap-include' );
	}

	/**
	 * Moves the hide- links options from the permalinks option to the titles option
	 */
	private function move_hide_links_options() {
		$options_titles = get_option( 'ymbeseo_titles' );
		$options_permalinks = get_option( 'ymbeseo_permalinks' );

		foreach ( array( 'hide-feedlinks', 'hide-rsdlink', 'hide-shortlink', 'hide-wlwmanifest' ) as $hide ) {
			if ( isset( $options_titles[ $hide ] ) ) {
				$options_permalinks[ $hide ] = $options_titles[ $hide ];
				unset( $options_titles[ $hide ] );
				update_option( 'ymbeseo_permalinks', $options_permalinks );
				update_option( 'ymbeseo_titles', $options_titles );
			}
		}
	}

	/**
	 * Move the pinterest verification option from the ymbeseo option to the ymbeseo_social option
	 */
	private function move_pinterest_option() {
		$options_social = get_option( 'ymbeseo_social' );

		if ( isset( $option_ymbeseo['pinterestverify'] ) ) {
			$options_social['pinterestverify'] = $option_ymbeseo['pinterestverify'];
			unset( $option_ymbeseo['pinterestverify'] );
			update_option( 'ymbeseo_social', $options_social );
			update_option( 'ymbeseo', $option_ymbeseo );
		}
	}

	/**
	 * Runs the needed cleanup after an update, setting the DB version to latest version, flushing caches etc.
	 */
	private function finish_up() {
		$this->options = get_option( 'ymbeseo' );                             // Re-get to make sure we have the latest version.
		update_option( 'ymbeseo', $this->options );                           // This also ensures the DB version is equal to YMBESEO_VERSION.

		add_action( 'shutdown', 'flush_rewrite_rules' );                    // Just flush rewrites, always, to at least make them work after an upgrade.
		YMBESEO_Utils::clear_sitemap_cache();                                 // Flush the sitemap cache.

		YMBESEO_Options::ensure_options_exist();                              // Make sure all our options always exist - issue #1245.
	}

}
