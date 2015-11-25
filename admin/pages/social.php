<?php
/**
 * @package YMBESEO\Admin
 */

if ( ! defined( 'YMBESEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$yform = Yoast_Form::get_instance();

$social_facebook = new Yoast_Social_Facebook( );

$yform->admin_header( true, 'YMBESEO_social' );
?>

	<h2 class="nav-tab-wrapper" id="wpseo-tabs">
		<a class="nav-tab" id="accounts-tab" href="#top#accounts"><?php _e( 'Accounts', 'ymbeseo' ); ?></a>
		<a class="nav-tab" id="facebook-tab" href="#top#facebook"><span class="dashicons dashicons-facebook-alt"></span> <?php _e( 'Facebook', 'ymbeseo' ); ?></a>
		<a class="nav-tab" id="twitterbox-tab" href="#top#twitterbox"><span class="dashicons dashicons-twitter"></span> <?php _e( 'Twitter', 'ymbeseo' ); ?></a>
		<a class="nav-tab" id="pinterest-tab" href="#top#pinterest"><?php _e( 'Pinterest', 'ymbeseo' ); ?></a>
		<a class="nav-tab" id="google-tab" href="#top#google"><span class="dashicons dashicons-googleplus"></span> <?php _e( 'Google+', 'ymbeseo' ); ?></a>
	</h2>

	<div id="accounts" class="wpseotab">
		<p>
			<?php _e( 'To inform Google about your social profiles, we need to know their URLs.', 'ymbeseo' ); ?>
			<?php _e( 'For each, pick the main account associated with this site and please enter them below:', 'ymbeseo' ); ?>
		</p>
		<?php
		$yform->textinput( 'facebook_site', __( 'Facebook Page URL', 'ymbeseo' ) );
		$yform->textinput( 'twitter_site', __( 'Twitter Username', 'ymbeseo' ) );
		$yform->textinput( 'instagram_url', __( 'Instagram URL', 'ymbeseo' ) );
		$yform->textinput( 'linkedin_url', __( 'LinkedIn URL', 'ymbeseo' ) );
		$yform->textinput( 'myspace_url', __( 'MySpace URL', 'ymbeseo' ) );
		$yform->textinput( 'pinterest_url', __( 'Pinterest URL', 'ymbeseo' ) );
		$yform->textinput( 'youtube_url', __( 'YouTube URL', 'ymbeseo' ) );
		$yform->textinput( 'google_plus_url', __( 'Google+ URL', 'ymbeseo' ) );

		do_action( 'YMBESEO_admin_other_section' );
		?>
	</div>

	<div id="facebook" class="wpseotab">
		<p>
			<?php
				/* translators: %s expands to <code>&lt;head&gt;</code> */
				printf( __( 'Add Open Graph meta data to your site\'s %s section, Facebook and other social networks use this data when your pages are shared.', 'ymbeseo' ), '<code>&lt;head&gt;</code>' );
			?>
		</p>
		<?php $yform->checkbox( 'opengraph', __( 'Add Open Graph meta data', 'ymbeseo' ) ); ?>

		<?php
		if ( 'posts' == get_option( 'show_on_front' ) ) {
			echo '<p><strong>' . esc_html__( 'Frontpage settings', 'ymbeseo' ) . '</strong></p>';
			echo '<p>' . esc_html__( 'These are the title, description and image used in the Open Graph meta tags on the front page of your site.', 'ymbeseo' ) . '</p>';

			$yform->media_input( 'og_frontpage_image', __( 'Image URL', 'ymbeseo' ) );
			$yform->textinput( 'og_frontpage_title', __( 'Title', 'ymbeseo' ) );
			$yform->textinput( 'og_frontpage_desc', __( 'Description', 'ymbeseo' ) );

			// Offer copying of meta description.
			$meta_options = get_option( 'YMBESEO_titles' );
			echo '<input type="hidden" id="meta_description" value="', esc_attr( $meta_options['metadesc-home-wpseo'] ), '" />';
			echo '<p class="label desc" style="border:0;"><a href="javascript:;" onclick="wpseoCopyHomeMeta();" class="button">', esc_html__( 'Copy home meta description', 'ymbeseo' ), '</a></p>';

		} ?>

		<p><strong><?php esc_html_e( 'Default settings', 'ymbeseo' ); ?></strong></p>
		<?php $yform->media_input( 'og_default_image', __( 'Image URL', 'ymbeseo' ) ); ?>
		<p class="desc label">
			<?php esc_html_e( 'This image is used if the post/page being shared does not contain any images.', 'ymbeseo' ); ?>
		</p>

		<?php $social_facebook->show_form(); ?>

		<?php do_action( 'YMBESEO_admin_opengraph_section' ); ?>
	</div>

	<div id="twitterbox" class="wpseotab">
		<p>
			<?php
			/* translators: %s expands to <code>&lt;head&gt;</code> */
			printf( __( 'Add Twitter card meta data to your site\'s %s section.', 'ymbeseo' ), '<code>&lt;head&gt;</code>' );
			?>
		</p>

		<?php $yform->checkbox( 'twitter', __( 'Add Twitter card meta data', 'ymbeseo' ) ); ?>

		<?php
		$yform->select( 'twitter_card_type', __( 'The default card type to use', 'ymbeseo' ), YMBESEO_Option_Social::$twitter_card_types );
		do_action( 'YMBESEO_admin_twitter_section' );
		?>
	</div>

	<div id="pinterest" class="wpseotab">
		<p>
			<?php _e( 'Pinterest uses Open Graph metadata just like Facebook, so be sure to keep the Open Graph checkbox on the Facebook tab checked if you want to optimize your site for Pinterest.', 'ymbeseo' ); ?>
		</p>
		<p>
			<?php
				/* translators: %1$s / %2$s expands to a link to pinterest.com's help page. */
				printf( __( 'To %1$sverify your site with Pinterest%2$s, add the meta tag here:', 'ymbeseo' ), '<a target="_blank" href="https://help.pinterest.com/en/articles/verify-your-website#meta_tag">', '</a>' );
			?>
		</p>

		<?php $yform->textinput( 'pinterestverify', __( 'Pinterest verification', 'ymbeseo' ) ); ?>

		<?php
		do_action( 'YMBESEO_admin_pinterest_section' );
		?>
	</div>

	<div id="google" class="wpseotab">
		<p>
			<?php $yform->checkbox( 'googleplus', __( 'Add Google+ specific post meta data', 'ymbeseo' ) ); ?>
		</p>

		<p><?php _e( 'If you have a Google+ page for your business, add that URL here and link it on your Google+ page\'s about page.', 'ymbeseo' ); ?></p>

		<?php $yform->textinput( 'plus-publisher', __( 'Google Publisher Page', 'ymbeseo' ) ); ?>

		<?php do_action( 'YMBESEO_admin_googleplus_section' ); ?>
	</div>

<?php
$yform->admin_footer();
