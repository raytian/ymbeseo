<?php
/**
 * @package YMBESEO\Admin
 */

/**
 * @todo - [JRF => whomever] check for other sitemap plugins which may conflict ?
 * @todo - [JRF => whomever] check for existance of .xls rewrite rule in .htaccess from
 * google-sitemaps-plugin/generator and remove as it will cause errors for our sitemaps
 * (or inform the user and disallow enabling of sitemaps )
 * @todo - [JRF => whomever] check if anything along these lines is already being done
 */


if ( ! defined( 'YMBESEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$yform = Yoast_Form::get_instance();

$yform->admin_header( true, 'YMBESEO_xml' );

$options = get_option( 'YMBESEO_xml' );

echo '<br/>';

$yform->checkbox( 'enablexmlsitemap', __( 'Check this box to enable XML sitemap functionality.', 'ymbeseo' ), false );

?>
	<div id="sitemapinfo">
		<br/>

		<h2 class="nav-tab-wrapper" id="wpseo-tabs">
			<a class="nav-tab" id="general-tab" href="#top#general"><?php _e( 'General', 'ymbeseo' ); ?></a>
			<a class="nav-tab" id="user-sitemap-tab"
			   href="#top#user-sitemap"><?php _e( 'User sitemap', 'ymbeseo' ); ?></a>
			<a class="nav-tab" id="post-types-tab"
			   href="#top#post-types"><?php _e( 'Post Types', 'ymbeseo' ); ?></a>
			<a class="nav-tab" id="exclude-post-tab" href="#top#exclude-post"><?php _e( 'Excluded Posts', 'ymbeseo' ); ?></a>
			<a class="nav-tab" id="taxonomies-tab"
			   href="#top#taxonomies"><?php _e( 'Taxonomies', 'ymbeseo' ); ?></a>
		</h2>

		<div id="general" class="wpseotab">
			<?php

			if ( $options['enablexmlsitemap'] === true ) {
				echo '<p>';
				printf( esc_html__( 'You can find your XML Sitemap here: %sXML Sitemap%s', 'ymbeseo' ), '<a target="_blank" class="button-secondary" href="' . esc_url( YMBESEO_xml_sitemaps_base_url( 'sitemap_index.xml' ) ) . '">', '</a>' );
				echo '<br/>';
				echo '<br/>';
				_e( 'You do <strong>not</strong> need to generate the XML sitemap, nor will it take up time to generate after publishing a post.', 'ymbeseo' );
				echo '</p>';
			}
			else {
				echo '<p>', __( 'Save your settings to activate XML Sitemaps.', 'ymbeseo' ), '</p>';
			}
			?>

			<p>
				<strong><?php _e( 'Entries per page', 'ymbeseo' ); ?></strong><br/>
				<?php printf( __( 'Please enter the maximum number of entries per sitemap page (defaults to %s, you might want to lower this to prevent memory issues on some installs):', 'ymbeseo' ), YMBESEO_Options::get_default( 'YMBESEO_xml', 'entries-per-page' ) ); ?>
			</p>

			<?php
			$yform->textinput( 'entries-per-page', __( 'Max entries per sitemap', 'ymbeseo' ) );
			?>
		</div>

		<div id="user-sitemap" class="wpseotab">
			<?php
			$yform->checkbox( 'disable_author_sitemap', __( 'Disable author/user sitemap', 'ymbeseo' ), false );
			?>
			<div id="xml_user_block">
				<p><strong><?php _e( 'Exclude users without posts', 'ymbeseo' ); ?></strong><br/>
					<?php $yform->checkbox( 'disable_author_noposts', __( 'Disable all users with zero posts', 'ymbeseo' ), false );

					$roles = YMBESEO_Utils::get_roles();
					if ( is_array( $roles ) && $roles !== array() ) {
						echo '<p><strong>' . __( 'Exclude user roles', 'ymbeseo' ) . '</strong><br/>';
						echo __( 'Please check the appropriate box below if there\'s a user role that you do <strong>NOT</strong> want to include in your sitemap:', 'ymbeseo' ) . '</p>';
						foreach ( $roles as $role_key => $role_name ) {
							$yform->checkbox( 'user_role-' . $role_key . '-not_in_sitemap', $role_name );
						}
					} ?>
			</div>
		</div>

		<div id="post-types" class="wpseotab">

			<?php
			$post_types = apply_filters( 'YMBESEO_sitemaps_supported_post_types', get_post_types( array( 'public' => true ), 'objects' ) );
			if ( is_array( $post_types ) && $post_types !== array() ) {
				echo '<p>' . __( 'Please check the appropriate box below if there\'s a post type that you do <strong>NOT</strong> want to include in your sitemap:', 'ymbeseo' ) . '</p>';
				foreach ( $post_types as $pt ) {
					$yform->checkbox( 'post_types-' . $pt->name . '-not_in_sitemap', $pt->labels->name . ' (<code>' . $pt->name . '</code>)' );
				}
			}

			?>

		</div>

		<div id="exclude-post" class="wpseotab">
			<?php
			/* Translators: %1$s: expands to '<code>1,2,99,100</code>' */
			echo '<p>' , sprintf( __( 'You can exclude posts from the sitemap by entering a comma separated string with the Post ID\'s. The format will become something like: %1$s.', 'ymbeseo' ), '<code>1,2,99,100</code>' ) , '</p>';
			$yform->textinput( 'excluded-posts', __( 'Posts to exclude', 'ymbeseo' ) );
			?>
		</div>

		<div id="taxonomies" class="wpseotab">

			<?php
			$taxonomies = apply_filters( 'YMBESEO_sitemaps_supported_taxonomies', get_taxonomies( array( 'public' => true ), 'objects' ) );
			if ( is_array( $taxonomies ) && $taxonomies !== array() ) {
				echo '<p>' . __( 'Please check the appropriate box below if there\'s a taxonomy that you do <strong>NOT</strong> want to include in your sitemap:', 'ymbeseo' ) . '</p>';
				foreach ( $taxonomies as $tax ) {
					if ( isset( $tax->labels->name ) && trim( $tax->labels->name ) != '' ) {
						$yform->checkbox( 'taxonomies-' . $tax->name . '-not_in_sitemap', $tax->labels->name . ' (<code>' . $tax->name . '</code>)' );
					}
				}
			}

			?>
		</div>
	</div>
<?php

do_action( 'YMBESEO_xmlsitemaps_config' );

$yform->admin_footer();
