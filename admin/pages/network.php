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

$options = get_site_option( 'ymbeseo_ms' );

if ( isset( $_POST['ymbeseo_submit'] ) ) {
	check_admin_referer( 'ymbeseo-network-settings' );

	foreach ( array( 'access', 'defaultblog' ) as $opt ) {
		$options[ $opt ] = $_POST['ymbeseo_ms'][ $opt ];
	}
	unset( $opt );
	YMBESEO_Options::update_site_option( 'ymbeseo_ms', $options );
	add_settings_error( 'ymbeseo_ms', 'settings_updated', __( 'Settings Updated.', 'ymbeseo' ), 'updated' );
}

if ( isset( $_POST['ymbeseo_restore_blog'] ) ) {
	check_admin_referer( 'ymbeseo-network-restore' );
	if ( isset( $_POST['ymbeseo_ms']['restoreblog'] ) && is_numeric( $_POST['ymbeseo_ms']['restoreblog'] ) ) {
		$restoreblog = (int) YMBESEO_Utils::validate_int( $_POST['ymbeseo_ms']['restoreblog'] );
		$blog        = get_blog_details( $restoreblog );

		if ( $blog ) {
			YMBESEO_Options::reset_ms_blog( $restoreblog );
			add_settings_error( 'ymbeseo_ms', 'settings_updated', sprintf( __( '%s restored to default SEO settings.', 'ymbeseo' ), esc_html( $blog->blogname ) ), 'updated' );
		}
		else {
			add_settings_error( 'ymbeseo_ms', 'settings_updated', sprintf( __( 'Blog %s not found.', 'ymbeseo' ), esc_html( $restoreblog ) ), 'error' );
		}
		unset( $restoreblog, $blog );
	}
}

/* Set up selectbox dropdowns for smaller networks (usability) */
$use_dropdown = true;
if ( get_blog_count() > 100 ) {
	$use_dropdown = false;
}
else {
	$sites = wp_get_sites( array( 'deleted' => 0 ) );
	if ( is_array( $sites ) && $sites !== array() ) {
		$dropdown_input = array(
			'-' => __( 'None', 'ymbeseo' ),
		);

		foreach ( $sites as $site ) {
			$dropdown_input[ $site['blog_id'] ] = $site['blog_id'] . ': ' . $site['domain'];

			$blog_states = array();
			if ( $site['public'] === '1' ) {
				$blog_states[] = __( 'public', 'ymbeseo' );
			}
			if ( $site['archived'] === '1' ) {
				$blog_states[] = __( 'archived', 'ymbeseo' );
			}
			if ( $site['mature'] === '1' ) {
				$blog_states[] = __( 'mature', 'ymbeseo' );
			}
			if ( $site['spam'] === '1' ) {
				$blog_states[] = __( 'spam', 'ymbeseo' );
			}
			if ( $blog_states !== array() ) {
				$dropdown_input[ $site['blog_id'] ] .= ' [' . implode( ', ', $blog_states ) . ']';
			}
		}
		unset( $site, $blog_states );
	}
	else {
		$use_dropdown = false;
	}
	unset( $sites );
}

$yform->admin_header( false, 'ymbeseo_ms' );

echo '<h2>', __( 'MultiSite Settings', 'ymbeseo' ), '</h2>';
echo '<form method="post" accept-charset="', esc_attr( get_bloginfo( 'charset' ) ), '">';
wp_nonce_field( 'ymbeseo-network-settings', '_wpnonce', true, true );

/* @internal Important: Make sure the options added to the array here are in line with the options set in the YMBESEO_Option_MS::$allowed_access_options property */
$yform->select(
	'access',
	/* translators: %1$s expands to Yoast SEO */
	sprintf( __( 'Who should have access to the %1$s settings', 'ymbeseo' ), 'Yoast SEO' ),
	array(
		'admin'      => __( 'Site Admins (default)', 'ymbeseo' ),
		'superadmin' => __( 'Super Admins only', 'ymbeseo' ),
	),
	'ymbeseo_ms'
);

if ( $use_dropdown === true ) {
	$yform->select(
		'defaultblog',
		__( 'New sites in the network inherit their SEO settings from this site', 'ymbeseo' ),
		$dropdown_input,
		'ymbeseo_ms'
	);
	echo '<p>' . __( 'Choose the site whose settings you want to use as default for all sites that are added to your network. If you choose \'None\', the normal plugin defaults will be used.', 'ymbeseo' ) . '</p>';
}
else {
	$yform->textinput( 'defaultblog', __( 'New sites in the network inherit their SEO settings from this site', 'ymbeseo' ), 'ymbeseo_ms' );
	echo '<p>' . sprintf( __( 'Enter the %sSite ID%s for the site whose settings you want to use as default for all sites that are added to your network. Leave empty for none (i.e. the normal plugin defaults will be used).', 'ymbeseo' ), '<a href="' . esc_url( network_admin_url( 'sites.php' ) ) . '">', '</a>' ) . '</p>';
}
	echo '<p><strong>' . __( 'Take note:', 'ymbeseo' ) . '</strong> ' . __( 'Privacy sensitive (FB admins and such), theme specific (title rewrite) and a few very site specific settings will not be imported to new blogs.', 'ymbeseo' ) . '</p>';


echo '<input type="submit" name="ymbeseo_submit" class="button-primary" value="' . __( 'Save MultiSite Settings', 'ymbeseo' ) . '"/>';
echo '</form>';

echo '<h2>' . __( 'Restore site to default settings', 'ymbeseo' ) . '</h2>';
echo '<form method="post" accept-charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '">';
wp_nonce_field( 'ymbeseo-network-restore', '_wpnonce', true, true );
echo '<p>' . __( 'Using this form you can reset a site to the default SEO settings.', 'ymbeseo' ) . '</p>';

if ( $use_dropdown === true ) {
	unset( $dropdown_input['-'] );
	$yform->select(
		'restoreblog',
		__( 'Site ID', 'ymbeseo' ),
		$dropdown_input,
		'ymbeseo_ms'
	);
}
else {
	$yform->textinput( 'restoreblog', __( 'Blog ID', 'ymbeseo' ), 'ymbeseo_ms' );
}

echo '<input type="submit" name="ymbeseo_restore_blog" value="' . __( 'Restore site to defaults', 'ymbeseo' ) . '" class="button"/>';
echo '</form>';


$yform->admin_footer( false );
