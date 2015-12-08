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
	 * Holds the YMBE SEO options
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

		/**
		 * Filter: 'ymbeseo_run_upgrade' - Runs the upgrade hook which are
		 * dependent on YMBE SEO.
		 *
		 * @api string - The current version of YMBE SEO
		 */
		do_action( 'ymbeseo_run_upgrade', $this->options['version'] );

		$this->finish_up();
	}

	/**
	 * Run some functions that run when we first run or when we upgrade
	 * YMBE SEO from < 1.4.13
	 */
	private function init() {
	}

	/**
	 * Runs the needed cleanup after an update, setting the DB version to
	 * latest version, flushing caches etc.
	 */
	private function finish_up() {
		$this->options = get_option( 'ymbeseo' );
		update_option( 'ymbeseo', $this->options );

		add_action( 'shutdown', 'flush_rewrite_rules' );
		YMBESEO_Utils::clear_sitemap_cache();

		YMBESEO_Options::ensure_options_exist();
	}
}
