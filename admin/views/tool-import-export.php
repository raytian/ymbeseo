<?php
/**
 * @package YMBESEO\Admin
 */

if ( ! defined( 'YMBESEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * @todo [JRF => testers] Extensively test the export & import of the (new) settings!
 * If that all works fine, getting testers to export before and after upgrade will make testing easier.
 *
 * @todo [Yoast] The import for the RSS Footer plugin checks for data already entered via YMBE SEO,
 * the other import routines should do that too.
 */

$yform = Yoast_Form::get_instance();

if ( isset( $_FILES['settings_import_file'] ) ) {
	check_admin_referer( 'ymbeseo-import-file' );

	$import = new YMBESEO_Import();
}

if ( isset( $import ) ) {
	/**
	 * Allow customization of import&export message
	 * @api  string  $msg  The message.
	 */
	$msg = apply_filters( 'ymbeseo_import_message', $import->msg );

	if ( ! empty( $msg ) ) {
		printf( '<div id="message" class="message updated" style="width:94%;"><p>%s</p></div>',
			esc_html( $msg )
		);
	}
}

?>
<br/><br/>
<h2 class="nav-tab-wrapper" id="ymbeseo-tabs">
	<a class="nav-tab nav-tab-active" id="ymbeseo-import-tab" href="#top#ymbeseo-import">
		<?php esc_html_e( 'Import', 'ymbeseo' ); ?>
	</a>
	<a class="nav-tab" id="ymbeseo-export-tab" href="#top#ymbeseo-export">
		<?php esc_html_e( 'Export', 'ymbeseo' ); ?>
	</a>
	<?php
	/**
	 * Allow adding a custom import tab header
	 */
	do_action( 'ymbeseo_import_tab_header' );
	?>
</h2>

<div id="ymbeseo-import" class="ymbeseotab">
	<p><?php esc_html_e( 'Import settings by locating <em>settings.zip</em> and clicking "Import settings"', 'ymbeseo' ); ?></p>

	<form
		action="<?php echo esc_attr( admin_url( 'admin.php?page=ymbeseo_tools&tool=import-export#top#ymbeseo-import' ) ); ?>"
		method="post" enctype="multipart/form-data"
		accept-charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
		<?php wp_nonce_field( 'ymbeseo-import-file', '_wpnonce', true, true ); ?>
		<input type="file" name="settings_import_file" accept="application/x-zip,application/x-zip-compressed,application/zip" />
		<input type="hidden" name="action" value="wp_handle_upload"/><br/>
		<br/>
		<input type="submit" class="button-primary" value="<?php _e( 'Import settings', 'ymbeseo' ); ?>"/>
	</form>
</div>

<div id="ymbeseo-export" class="ymbeseotab">
	<p><?php
		/* translators: %1$s expands to YMBE SEO */
		printf( esc_html__( 'Export your %1$s settings here, to import them again later or to import them on another site.', 'ymbeseo' ), 'YMBE SEO' );
	?></p>
	<?php $yform->checkbox( 'include_taxonomy_meta', __( 'Include Taxonomy Metadata', 'ymbeseo' ) ); ?><br/>
	<button class="button-primary" id="export-button"><?php
		/* translators: %1$s expands to YMBE SEO */
		printf( esc_html__( 'Export your %1$s settings', 'ymbeseo' ), 'YMBE SEO' );
		?></button>
	<script>
		var ymbeseo_export_nonce = '<?php echo wp_create_nonce( 'ymbeseo-export' ); ?>';
	</script>
</div>

<?php
/**
 * Allow adding a custom import tab
 */
do_action( 'ymbeseo_import_tab_content' );
