<?php
/**
 * @package YMBESEO\Admin
 */

if ( ! defined( 'YMBESEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$tool_page = (string) filter_input( INPUT_GET, 'tool' );

$yform = Yoast_Form::get_instance();

$yform->admin_header( false );

if ( '' === $tool_page ) {
	$tools = array(
		'bulk-editor' => array(
			'title' => __( 'Bulk editor', 'ymbeseo' ),
			'desc' => __( 'This tool allows you to quickly change titles and descriptions of your posts and pages without having to go into the editor for each page.', 'ymbeseo' ),
		),
		'import-export' => array(
			'title' => __( 'Import and Export', 'ymbeseo' ),
			'desc' => __( 'Import settings from other SEO plugins and export your settings for re-use on (another) blog.', 'ymbeseo' ),
		),
	);
	if ( YMBESEO_Utils::allow_system_file_edit() === true && ! is_multisite() ) {
		$tools['file-editor'] = array(
			'title' => __( 'File editor', 'ymbeseo' ),
			'desc' => __( 'This tool allows you to quickly change important files for your SEO, like your robots.txt.', 'ymbeseo' ),
		);
	}

	/* translators: %1$s expands to YMBE SEO */
	echo '<p>', sprintf( __( '%1$s comes with some very powerful built-in tools:', 'ymbeseo' ), 'YMBE SEO' ), '</p>';

	asort( $tools );

	echo '<ul class="ul-disc">';
	foreach ( $tools as $slug => $tool ) {
		echo '<li>';
		echo '<strong><a href="', admin_url( 'admin.php?page=ymbeseo_tools&tool=' . $slug ), '">', $tool['title'], '</a></strong><br/>';
		echo $tool['desc'];
		echo '</li>';
	}
	echo '</ul>';

}
else {
	echo '<a href="', admin_url( 'admin.php?page=ymbeseo_tools' ), '">', __( '&laquo; Back to Tools page', 'ymbeseo' ), '</a>';
	require_once YMBESEO_PATH . 'admin/views/tool-' . $tool_page . '.php';
}

$yform->admin_footer( false );
