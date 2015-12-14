<?php
/**
 * @package YMBESEO\Admin
 */

if ( ! defined( 'YMBESEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$robots_file    = get_home_path() . 'robots.txt';

if ( isset( $_POST['create_robots'] ) ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		die( __( 'You cannot create a robots.txt file.', 'ymbeseo' ) );
	}

	check_admin_referer( 'ymbeseo_create_robots' );

	ob_start();
	error_reporting( 0 );
	do_robots();
	$robots_content = ob_get_clean();

	$f = fopen( $robots_file, 'x' );
	fwrite( $f, $robots_content );
}

if ( isset( $_POST['submitrobots'] ) ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		die( __( 'You cannot edit the robots.txt file.', 'ymbeseo' ) );
	}

	check_admin_referer( 'ymbeseo-robotstxt' );

	if ( file_exists( $robots_file ) ) {
		$robotsnew = stripslashes( $_POST['robotsnew'] );
		if ( is_writable( $robots_file ) ) {
			$f = fopen( $robots_file, 'w+' );
			fwrite( $f, $robotsnew );
			fclose( $f );
			$msg = __( 'Updated Robots.txt', 'ymbeseo' );
		}
	}
}

if ( isset( $msg ) && ! empty( $msg ) ) {
	echo '<div id="message" style="width:94%;" class="updated fade"><p>', esc_html( $msg ), '</p></div>';
}

if ( is_multisite() ) {
	$action_url = network_admin_url( 'admin.php?page=ymbeseo_files' );
}
else {
	$action_url = admin_url( 'admin.php?page=ymbeseo_tools&tool=file-editor' );
}

echo '<h2>', __( 'Robots.txt', 'ymbeseo' ), '</h2>';

if ( ! file_exists( $robots_file ) ) {
	if ( is_writable( get_home_path() ) ) {
		echo '<form action="', esc_url( $action_url ), '" method="post" id="robotstxtcreateform">';
		wp_nonce_field( 'ymbeseo_create_robots', '_wpnonce', true, true );
		echo '<p>', __( 'You don\'t have a robots.txt file, create one here:', 'ymbeseo' ), '</p>';
		echo '<input type="submit" class="button" name="create_robots" value="', __( 'Create robots.txt file', 'ymbeseo' ), '">';
		echo '</form>';
	}
	else {
		echo '<p>', __( 'If you had a robots.txt file and it was editable, you could edit it from here.', 'ymbeseo' ), '</p>';
	}
}
else {
	$f = fopen( $robots_file, 'r' );

	$content = '';
	if ( filesize( $robots_file ) > 0 ) {
		$content = fread( $f, filesize( $robots_file ) );
	}
	$robots_txt_content = esc_textarea( $content );

	if ( ! is_writable( $robots_file ) ) {
		echo '<p><em>', __( 'If your robots.txt were writable, you could edit it from here.', 'ymbeseo' ), '</em></p>';
		echo '<textarea class="large-text code" disabled="disabled" rows="15" name="robotsnew">', $robots_txt_content, '</textarea><br/>';
	}
	else {
		echo '<form action="', esc_url( $action_url ), '" method="post" id="robotstxtform">';
		wp_nonce_field( 'ymbeseo-robotstxt', '_wpnonce', true, true );
		echo '<p>', __( 'Edit the content of your robots.txt:', 'ymbeseo' ), '</p>';
		echo '<textarea class="large-text code" rows="15" name="robotsnew">', $robots_txt_content, '</textarea><br/>';
		echo '<div class="submit"><input class="button" type="submit" name="submitrobots" value="', __( 'Save changes to Robots.txt', 'ymbeseo' ), '" /></div>';
		echo '</form>';
	}
}
