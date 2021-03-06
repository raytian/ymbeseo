<?php
/**
 * @package YMBESEO\Admin
 * @since      1.5.0
 */

if ( ! defined( 'YMBESEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$options = get_option( 'wpseo' );

$YMBESEO_bulk_titles_table      = new YMBESEO_Bulk_Title_Editor_List_Table();
$YMBESEO_bulk_description_table = new YMBESEO_Bulk_Description_List_Table();

// If type is empty, fill it with value of first tab (title).
$_GET['type'] = ( ! empty( $_GET['type'] ) ) ? $_GET['type'] : 'title';

if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
	wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER['REQUEST_URI'] ) ) );
	exit;
}
?>
<script>
	var YMBESEO_bulk_editor_nonce = '<?php echo wp_create_nonce( 'wpseo-bulk-editor' ); ?>';
</script>

<div class="wrap YMBESEO_table_page">

	<h2 class="nav-tab-wrapper" id="wpseo-tabs">
		<a class="nav-tab" id="title-tab" href="#top#title"><?php _e( 'Title', 'ymbeseo' ); ?></a>
		<a class="nav-tab" id="description-tab"
		   href="#top#description"><?php _e( 'Description', 'ymbeseo' ); ?></a>
	</h2>

	<div class="tabwrapper">
		<div id="title" class="wpseotab">
			<?php $YMBESEO_bulk_titles_table->show_page(); ?>
		</div>
		<div id="description" class="wpseotab">
			<?php $YMBESEO_bulk_description_table->show_page(); ?>
		</div>

	</div>
</div>
