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

$options = get_option( 'ymbeseo' );

$ymbeseo_bulk_titles_table      = new YMBESEO_Bulk_Title_Editor_List_Table();
$ymbeseo_bulk_description_table = new YMBESEO_Bulk_Description_List_Table();

// If type is empty, fill it with value of first tab (title).
$_GET['type'] = ( ! empty( $_GET['type'] ) ) ? $_GET['type'] : 'title';

if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
	wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER['REQUEST_URI'] ) ) );
	exit;
}
?>
<script>
	var ymbeseo_bulk_editor_nonce = '<?php echo wp_create_nonce( 'ymbeseo-bulk-editor' ); ?>';
</script>

<div class="wrap ymbeseo_table_page">

	<h2 class="nav-tab-wrapper" id="ymbeseo-tabs">
		<a class="nav-tab" id="title-tab" href="#top#title"><?php _e( 'Title', 'wordpress-seo' ); ?></a>
		<a class="nav-tab" id="description-tab"
		   href="#top#description"><?php _e( 'Description', 'wordpress-seo' ); ?></a>
	</h2>

	<div class="tabwrapper">
		<div id="title" class="ymbeseotab">
			<?php $ymbeseo_bulk_titles_table->show_page(); ?>
		</div>
		<div id="description" class="ymbeseotab">
			<?php $ymbeseo_bulk_description_table->show_page(); ?>
		</div>

	</div>
</div>
