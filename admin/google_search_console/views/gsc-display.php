<?php
/**
 * @package YMBESEO\Admin|Google_Search_Console
 */

	// Admin header.
	Yoast_Form::get_instance()->admin_header( false, 'ymbeseo-gsc', false, 'yoast_ymbeseo_gsc_options' );
?>
	<h2 class="nav-tab-wrapper" id="ymbeseo-tabs">
<?php
if ( defined( 'WP_DEBUG' ) && WP_DEBUG && YMBESEO_GSC_Settings::get_profile() !== '' ) {
	?>
		<form action="" method="post">
			<input type='hidden' name='reload-crawl-issues-nonce' value='<?php echo wp_create_nonce( 'reload-crawl-issues' ); ?>' />
			<input type="submit" name="reload-crawl-issues" id="reload-crawl-issue" class="button-primary"
				   style="float: right;" value="<?php _e( 'Reload crawl issues', 'ymbeseo' ); ?>">
		</form>
<?php } ?>
		<?php echo $platform_tabs = new YMBESEO_GSC_Platform_Tabs; ?>
	</h2>

<?php

switch ( $platform_tabs->current_tab() ) {
	case 'settings' :
		// Check if there is an access token.
		if ( null === $this->service->get_client()->getAccessToken() ) {
			// Print auth screen.
			echo '<p>';
			/* Translators: %1$s: expands to 'Yoast SEO', %2$s expands to Google Search Console. */
			echo sprintf( __( 'To allow %1$s to fetch your %2$s information, please enter your Google Authorization Code.', 'ymbeseo' ), 'Yoast SEO', 'Google Search Console' );
			echo "</p>\n";
			echo '<input type="hidden" id="gsc_auth_url" value="', $this->service->get_client()->createAuthUrl() , '" />';
			echo "<button id='gsc_auth_code' class='button-secondary'>" , __( 'Get Google Authorization Code', 'ymbeseo' ) ,"</button>\n";

			echo '<p>' . __( 'Please enter the Google Authorization Code in the field below and press the Authenticate button.', 'ymbeseo' ) . "</p>\n";
			echo "<form action='" . admin_url( 'admin.php?page=ymbeseo_search_console&tab=settings' ) . "' method='post'>\n";
			echo "<input type='text' name='gsc[authorization_code]' value='' />";
			echo "<input type='hidden' name='gsc[gsc_nonce]' value='" . wp_create_nonce( 'ymbeseo-gsc_nonce' ) . "' />";
			echo "<input type='submit' name='gsc[Submit]' value='" . __( 'Authenticate', 'ymbeseo' ) . "' class='button-primary' />";
			echo "</form>\n";
		}
		else {
			$reset_button = '<a class="button-secondary" href="' . add_query_arg( 'gsc_reset', 1 ). '">' . __( 'Reauthenticate with Google ', 'ymbeseo' ) .'</a>';
			echo '<h3>',  __( 'Current profile', 'ymbeseo' ), '</h3>';
			if ( ($profile = YMBESEO_GSC_Settings::get_profile() ) !== '' ) {
				echo '<p>';
				echo Yoast_Form::get_instance()->label( __( 'Current profile', 'ymbeseo' ), array() );
				echo $profile;
				echo '</p>';

				echo '<p>';
				echo '<label class="select"></label>';
				echo $reset_button;
				echo '</p>';

			}
			else {
				echo "<form action='" . admin_url( 'options.php' ) . "' method='post'>";

				settings_fields( 'yoast_ymbeseo_gsc_options' );
				Yoast_Form::get_instance()->set_option( 'ymbeseo-gsc' );

				echo '<p>';
				if ( $profiles = $this->service->get_sites() ) {
					$show_save = true;
					echo Yoast_Form::get_instance()->select( 'profile', __( 'Profile', 'ymbeseo' ), $profiles );
				}
				else {
					$show_save = false;
					echo '<label class="select" for="profile">', __( 'Profile', 'ymbeseo' ), '</label>';
					echo __( 'There were no profiles found', 'ymbeseo' );
				}
				echo '</p>';

				echo '<p>';
				echo '<label class="select"></label>';

				if ( $show_save ) {
					echo '<input type="submit" name="submit" id="submit" class="button button-primary" value="' . __( 'Save Profile', 'ymbeseo' ) . '" /> ' . __( 'or', 'ymbeseo' ) , ' ';
				}
				echo $reset_button;
				echo '</p>';
				echo '</form>';
			}
		}
		break;

	default :
		$form_action_url = add_query_arg( 'page', esc_attr( filter_input( INPUT_GET, 'page' ) ) );

		// Open <form>.
		echo "<form id='ymbeseo-crawl-issues-table-form' action='" . $form_action_url . "' method='post'>\n";

		// AJAX nonce.
		echo "<input type='hidden' class='ymbeseo-gsc-ajax-security' value='" . wp_create_nonce( 'ymbeseo-gsc-ajax-security' ) . "' />\n";

		$this->display_table();

		// Close <form>.
		echo "</form>\n";

		break;
}
?>
	<br class="clear" />
<?php

// Admin footer.
Yoast_Form::get_instance()->admin_footer( false );
