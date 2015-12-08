<?php
/**
 * @package YMBESEO\Admin\Import\External
 */

/**
 * Class YMBESEO_Import_External
 *
 * Class with functionality to import Yoast SEO settings from other plugins
 */
class YMBESEO_Import_External {

	/**
	 * Whether or not to delete old data
	 *
	 * @var boolean
	 */
	protected $replace;

	/**
	 * Message about the import
	 *
	 * @var string
	 */
	public $msg = '';

	/**
	 * Class constructor
	 *
	 * @param boolean $replace
	 */
	public function __construct( $replace = false ) {
		$this->replace = $replace;

		YMBESEO_Options::initialize();
	}

	/**
	 * Convenience function to set import message
	 *
	 * @param string $msg
	 */
	protected function set_msg( $msg ) {
		if ( ! empty( $this->msg ) ) {
			$this->msg .= PHP_EOL;
		}
		$this->msg .= $msg;
	}

	/**
	 * Deletes an option depending on the class replace state
	 *
	 * @param string $option
	 */
	protected function perhaps_delete( $option ) {
		if ( $this->replace ) {
			delete_option( $option );
		}
	}

	/**
	 * Import HeadSpace SEO settings
	 */
	public function import_headspace() {
		global $wpdb;

		YMBESEO_Meta::replace_meta( '_headspace_description', YMBESEO_Meta::$meta_prefix . 'metadesc', $this->replace );
		YMBESEO_Meta::replace_meta( '_headspace_keywords', YMBESEO_Meta::$meta_prefix . 'metakeywords', $this->replace );
		YMBESEO_Meta::replace_meta( '_headspace_page_title', YMBESEO_Meta::$meta_prefix . 'title', $this->replace );

		/**
		 * @todo [JRF => whomever] verify how headspace sets these metas ( 'noindex', 'nofollow', 'noarchive', 'noodp', 'noydir' )
		 * and if the values saved are concurrent with the ones we use (i.e. 0/1/2)
		 */
		YMBESEO_Meta::replace_meta( '_headspace_noindex', YMBESEO_Meta::$meta_prefix . 'meta-robots-noindex', $this->replace );
		YMBESEO_Meta::replace_meta( '_headspace_nofollow', YMBESEO_Meta::$meta_prefix . 'meta-robots-nofollow', $this->replace );

		/*
		 * @todo - [JRF => whomever] check if this can be done more efficiently by querying only the meta table
		 * possibly directly changing it using concat on the existing values
		 */
		$posts = $wpdb->get_results( "SELECT ID FROM $wpdb->posts" );
		if ( is_array( $posts ) && $posts !== array() ) {
			foreach ( $posts as $post ) {
				$custom         = get_post_custom( $post->ID );
				$robotsmeta_adv = '';
				if ( isset( $custom['_headspace_noarchive'] ) ) {
					$robotsmeta_adv .= 'noarchive,';
				}
				if ( isset( $custom['_headspace_noodp'] ) ) {
					$robotsmeta_adv .= 'noodp,';
				}
				if ( isset( $custom['_headspace_noydir'] ) ) {
					$robotsmeta_adv .= 'noydir';
				}
				$robotsmeta_adv = preg_replace( '`,$`', '', $robotsmeta_adv );
				YMBESEO_Meta::set_value( 'meta-robots-adv', $robotsmeta_adv, $post->ID );
			}
		}

		if ( $this->replace ) {
			$hs_meta = array( 'noarchive', 'noodp', 'noydir' );
			foreach ( $hs_meta as $meta ) {
				delete_post_meta_by_key( '_headspace_' . $meta );
			}
			unset( $hs_meta, $meta );
		}
		$this->set_msg( __( 'HeadSpace2 data successfully imported', 'ymbeseo' ) );
	}

	/**
	 * Import from Joost's old robots meta plugin
	 */
	public function import_robots_meta() {
		global $wpdb;

		$posts = $wpdb->get_results( "SELECT ID, robotsmeta FROM $wpdb->posts" );

		if ( ! $posts ) {
			$this->set_msg( __( 'Error: no Robots Meta data found to import.', 'ymbeseo' ) );

			return;
		}
		if ( is_array( $posts ) && $posts !== array() ) {
			foreach ( $posts as $post ) {
				// Sync all possible settings.
				if ( $post->robotsmeta ) {
					$pieces = explode( ',', $post->robotsmeta );
					foreach ( $pieces as $meta ) {
						switch ( $meta ) {
							case 'noindex':
								YMBESEO_Meta::set_value( 'meta-robots-noindex', '1', $post->ID );
								break;

							case 'index':
								YMBESEO_Meta::set_value( 'meta-robots-noindex', '2', $post->ID );
								break;

							case 'nofollow':
								YMBESEO_Meta::set_value( 'meta-robots-nofollow', '1', $post->ID );
								break;
						}
					}
				}
			}
		}
		$this->set_msg( __( sprintf( 'Robots Meta values imported. We recommend %sdisabling the Robots-Meta plugin%s to avoid any conflicts.', '<a href="' . esc_url( admin_url( 'admin.php?page=ymbeseo_tools&tool=import-export&deactivate_robots_meta=1#top#import-other' ) ) . '">', '</a>' ), 'ymbeseo' ) );
	}

	/**
	 * Import from old Yoast RSS Footer plugin
	 */
	public function import_rss_footer() {
		$optold = get_option( 'RSSFooterOptions' );
		$optnew = get_option( 'ymbeseo_rss' );
		if ( $optold['position'] == 'after' ) {
			if ( $optnew['rssafter'] === '' || $optnew['rssafter'] === YMBESEO_Options::get_default( 'ymbeseo_rss', 'rssafter' ) ) {
				$optnew['rssafter'] = $optold['footerstring'];
			}
		}
		else {
			/* @internal Uncomment the second part if a default would be given to the rssbefore value */
			if ( $optnew['rssbefore'] === '' /*|| $optnew['rssbefore'] === YMBESEO_Options::get_default( 'ymbeseo_rss', 'rssbefore' )*/ ) {
				$optnew['rssbefore'] = $optold['footerstring'];
			}
		}
		update_option( 'ymbeseo_rss', $optnew );
		$this->set_msg( __( 'RSS Footer options imported successfully.', 'ymbeseo' ) );
	}

	/**
	 * Import from Yoast Breadcrumbs plugin
	 */
	public function import_yoast_breadcrumbs() {
		$optold = get_option( 'yoast_breadcrumbs' );
		$optnew = get_option( 'ymbeseo_internallinks' );

		if ( is_array( $optold ) && $optold !== array() ) {
			foreach ( $optold as $opt => $val ) {
				if ( is_bool( $val ) && $val === true ) {
					$optnew[ 'breadcrumbs-' . $opt ] = true;
				}
				else {
					$optnew[ 'breadcrumbs-' . $opt ] = $val;
				}
			}
			unset( $opt, $val );
			update_option( 'ymbeseo_internallinks', $optnew );
			$this->set_msg( __( 'Yoast Breadcrumbs options imported successfully.', 'ymbeseo' ) );
		}
		else {
			$this->set_msg( __( 'Yoast Breadcrumbs options could not be found', 'ymbeseo' ) );
		}
	}
}
