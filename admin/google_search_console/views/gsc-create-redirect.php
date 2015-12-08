<?php
/**
 * @package YMBESEO\Admin|Google_Search_Console
 *
 * This is the view for the modal box that appears when the create redirect link is clicked
 */

/**
 * @var string $view_type 		 The type of view to be displayed, can be 'create', 'already_exists', 'no_premium'
 * @var string $current_redirect The existing redirect
 * @var string $url 		     Redirect for URL
 */

?>
<div id='redirect-<?php echo md5( $url ); ?>' style='display: none;'>
	<form>
		<div class='form-wrap ymbeseo_content_wrapper'>
		<?php
		switch ( $view_type ) {
			case 'create' :
				echo '<h3>', __( 'Redirect this broken URL and fix the error', 'ymbeseo' ), '</h3>';
				?>
				<div class='form-field form-required'>
					<label for='ymbeseo-current-url'><?php _e( 'Current URL:', 'ymbeseo' ); ?></label>
					<input type='text' id='ymbeseo-current-url' name='current_url' value='<?php echo $url; ?>' disabled='disabled'>
				</div>
				<div class='form-field form-required'>
					<label for='ymbeseo-new-url'><?php _e( 'New URL:', 'ymbeseo' ); ?></label>
					<input type='text' id='ymbeseo-new-url' name='new_url' autofocus value=''>
				</div>
				<div class='form-field form-required'>
					<label for='ymbeseo-mark-as-fixed' class='clear'><?php _e( 'Mark as fixed:', 'ymbeseo' ); ?></label>
					<input type='checkbox' checked value='1' id='ymbeseo-mark-as-fixed' name='mark_as_fixed' class='clear' >
					<p><?php
						/* Translators: %1$s: expands to 'Google Search Console'. */
						echo sprintf( __( 'Mark this issue as fixed in %1$s.', 'ymbeseo' ), 'Google Search Console' );
						?></p>
				</div>
				<p class='submit'>
					<input type='button' name='submit' id='submit' class='button button-primary' value='<?php _e( 'Create redirect', 'ymbeseo' ); ?>' onclick='ymbeseo_gsc_post_redirect( jQuery( this ) );' />
				</p>
				<?php
				break;

			case 'already_exists' :
				echo '<h3>', __( 'Error: a redirect for this URL already exists', 'ymbeseo' ), '</h3>';
				echo '<p>';

				/* Translators: %1$s: expands to the current url and %2$s expands to url the redirects points to. */
				echo sprintf(
					__( 'You do not have to create a redirect for URL %1$s because a redirect already exists. The existing redirect points to %2$s. If this is fine you can mark this issue as fixed. If not, please go to the redirects page and change the target URL.', 'ymbeseo' ),
					$url,
					$current_redirect
				);
				echo '</p>';
				break;

			case 'no_premium' :
				/* Translators: %s: expands to Yoast SEO Premium */
				echo '<h3>', sprintf( __( 'Creating redirects is a %s feature', 'ymbeseo' ), 'Yoast SEO Premium' ), '</h3>';
				echo '<p>';
				/* Translators: %1$s: expands to 'Yoast SEO Premium', %2$s: links to Yoast SEO Premium plugin page. */
				echo sprintf(
					__( 'To be able to create a redirect and fix this issue, you need %1$s. You can buy the plugin, including one year support and updates, on %2$s.', 'ymbeseo' ),
					'Yoast SEO Premium',
					'<a href="http://yoa.st/redirects" target="_blank">yoast.com</a>'
				);
				echo '</p>';
				break;
		}
		?>
		</div>
	</form>
</div>
