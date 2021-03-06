<?php
/**
 * @package YMBESEO\Admin
 */

/**
 * This class adds the Social tab to the Yoast SEO metabox and makes sure the settings are saved.
 */
class YMBESEO_Social_Admin extends YMBESEO_Metabox {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'YMBESEO_tab_translate', array( $this, 'translate_meta_boxes' ) );
		add_action( 'YMBESEO_tab_header', array( $this, 'tab_header' ), 60 );
		add_action( 'YMBESEO_tab_content', array( $this, 'tab_content' ) );
		add_filter( 'YMBESEO_save_metaboxes', array( $this, 'save_meta_boxes' ), 10, 1 );
		add_action( 'YMBESEO_save_compare_data', array( $this, 'og_data_compare' ), 10, 1 );
	}

	/**
	 * Translate text strings for use in the meta box
	 *
	 * IMPORTANT: if you want to add a new string (option) somewhere, make sure you add that array key to
	 * the main meta box definition array in the class YMBESEO_Meta() as well!!!!
	 */
	public static function translate_meta_boxes() {
		/* translators: %s expands to the social network's name */
		$title_text       = __( 'If you don\'t want to use the post title for sharing the post on %s but instead want another title there, write it here.', 'ymbeseo' );
		/* translators: %s expands to the social network's name */
		$description_text = __( 'If you don\'t want to use the meta description for sharing the post on %s but want another description there, write it here.', 'ymbeseo' );
		/* translators: %s expands to the social network's name */
		$image_text       = __( 'If you want to override the image used on %s for this post, upload / choose an image or add the URL here.', 'ymbeseo' );
		/* translators: %1$s expands to the social network, %2$s to the recommended image size */
		$image_size_text  = __( 'The recommended image size for %1$s is %2$spx.', 'ymbeseo' );

		$options = YMBESEO_Options::get_all();

		$social_networks = array(
			'opengraph'  => __( 'Facebook', 'ymbeseo' ),
			'twitter'    => __( 'Twitter', 'ymbeseo' ),
			'googleplus' => __( 'Google+', 'ymbeseo' ),
		);

		// Source: https://blog.bufferapp.com/ideal-image-sizes-social-media-posts.
		$recommended_image_sizes = array(
			'opengraph'   => '1200 x 628',
			'twitter'     => '1024 x 512',
			'google-plus' => '800 x 1200',
		);

		foreach ( $social_networks as $network => $label ) {
			if ( true === $options[ $network ] ) {
				if ( 'googleplus' == $network ) {
					$network = 'google-plus'; // Yuck, I know.
				}

				self::$meta_fields['social'][ $network . '-title' ]['title']       = sprintf( __( '%s Title', 'ymbeseo' ), $label );
				self::$meta_fields['social'][ $network . '-title' ]['description'] = sprintf( $title_text, $label );

				self::$meta_fields['social'][ $network . '-description' ]['title']       = sprintf( __( '%s Description', 'ymbeseo' ), $label );
				self::$meta_fields['social'][ $network . '-description' ]['description'] = sprintf( $description_text, $label );

				self::$meta_fields['social'][ $network . '-image' ]['title']       = sprintf( __( '%s Image', 'ymbeseo' ), $label );
				self::$meta_fields['social'][ $network . '-image' ]['description'] = sprintf( $image_text, $label ) . ' ' . sprintf( $image_size_text, $label, $recommended_image_sizes[ $network ] );
			}
		}
	}

	/**
	 * Output the tab header for the Social tab
	 */
	public function tab_header() {
		echo '<li class="social"><a class="YMBESEO_tablink" href="#YMBESEO_social">', __( 'Social', 'ymbeseo' ), '</a></li>';
	}

	/**
	 * Output the tab content
	 */
	public function tab_content() {
		$content = '';
		foreach ( $this->get_meta_field_defs( 'social' ) as $meta_key => $meta_field ) {
			$content .= $this->do_meta_box( $meta_field, $meta_key );
		}
		$this->do_tab( 'social', __( 'Social', 'ymbeseo' ), $content );
	}


	/**
	 * Filter over the meta boxes to save, this function adds the Social meta boxes.
	 *
	 * @param   array $field_defs Array of metaboxes to save.
	 *
	 * @return  array
	 */
	public function save_meta_boxes( $field_defs ) {
		return array_merge( $field_defs, $this->get_meta_field_defs( 'social' ) );
	}

	/**
	 * This method will compare opengraph fields with the posted values.
	 *
	 * When fields are changed, the facebook cache will be purge.
	 *
	 * @param object $post
	 */
	public function og_data_compare( $post ) {

		// Check if post data is available, if post_id is set and if original post_status is publish.
		if (
			! empty( $_POST ) && ! empty( $post->ID ) && $post->post_status == 'publish' &&
			isset( $_POST['original_post_status'] ) && $_POST['original_post_status'] === 'publish'
		) {

			$fields_to_compare = array(
				'opengraph-title',
				'opengraph-description',
				'opengraph-image',
			);

			$reset_facebook_cache = false;

			foreach ( $fields_to_compare as $field_to_compare ) {
				$old_value = self::get_value( $field_to_compare, $post->ID );
				$new_value = self::get_post_value( self::$form_prefix . $field_to_compare );

				if ( $old_value !== $new_value ) {
					$reset_facebook_cache = true;
					break;
				}
			}
			unset( $field_to_compare, $old_value, $new_value );

			if ( $reset_facebook_cache ) {
				wp_remote_get(
					'https://graph.facebook.com/?id=' . get_permalink( $post->ID ) . '&scrape=true&method=post'
				);
			}
		}
	}
} /* End of class */
