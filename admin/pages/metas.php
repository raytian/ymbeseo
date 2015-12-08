<?php
/**
 * @package YMBESEO\Admin
 */

if ( ! defined( 'YMBESEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$options = YMBESEO_Options::get_all();

$yform = Yoast_Form::get_instance();

$yform->admin_header( true, 'ymbeseo_titles' );
?>

	<h2 class="nav-tab-wrapper" id="ymbeseo-tabs">
		<a class="nav-tab" id="general-tab" href="#top#general"><?php _e( 'General', 'ymbeseo' ); ?></a>
		<a class="nav-tab" id="home-tab" href="#top#home"><?php _e( 'Homepage', 'ymbeseo' ); ?></a>
		<a class="nav-tab" id="post_types-tab" href="#top#post_types"><?php _e( 'Post Types', 'ymbeseo' ); ?></a>
		<a class="nav-tab" id="taxonomies-tab" href="#top#taxonomies"><?php _e( 'Taxonomies', 'ymbeseo' ); ?></a>
		<a class="nav-tab" id="archives-tab" href="#top#archives"><?php _e( 'Archives', 'ymbeseo' ); ?></a>
		<a class="nav-tab" id="other-tab" href="#top#other"><?php _e( 'Other', 'ymbeseo' ); ?></a>
	</h2>

	<div class="tabwrapper">
		<div id="general" class="ymbeseotab">
			<table class="form-table">
				<tr>
					<th>
						<?php _e( 'Force rewrite titles', 'ymbeseo' ); ?>
					</th>
					<td>
						<?php
						$yform->checkbox( 'forcerewritetitle', __( 'Enable force rewrite titles', 'ymbeseo' ) );
						/* translators: %1$s expands to Yoast SEO */
						echo '<p class="description">', sprintf( __( '%1$s has auto-detected whether it needs to force rewrite the titles for your pages, if you think it\'s wrong and you know what you\'re doing, you can change the setting here.', 'ymbeseo' ), 'Yoast SEO' ) . '</p>';
						?>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Title Separator', 'ymbeseo' ); ?>
					</th>
					<td>
						<?php
						$yform->radio( 'separator', YMBESEO_Option_Titles::get_instance()->get_separator_options(), '' );
						echo '<p class="description">', __( 'Choose the symbol to use as your title separator. This will display, for instance, between your post title and site name.', 'ymbeseo' ), ' ', __( 'Symbols are shown in the size they\'ll appear in in search results.', 'ymbeseo' ), '</p>';
						?>
					</td>
				</tr>
			</table>
		</div>
		<div id="home" class="ymbeseotab">
			<?php
			if ( 'posts' == get_option( 'show_on_front' ) ) {
				echo '<p><strong>', __( 'Homepage', 'ymbeseo' ), '</strong><br/>';
				$yform->textinput( 'title-home-ymbeseo', __( 'Title template', 'ymbeseo' ), 'template homepage-template' );
				$yform->textarea( 'metadesc-home-ymbeseo', __( 'Meta description template', 'ymbeseo' ), array( 'class' => 'template homepage-template' ) );
				if ( $options['usemetakeywords'] === true ) {
					$yform->textinput( 'metakey-home-ymbeseo', __( 'Meta keywords template', 'ymbeseo' ) );
				}
				echo '</p>';
			}
			else {
				echo '<p><strong>', __( 'Homepage &amp; Front page', 'ymbeseo' ), '</strong><br/>';
				printf( __( 'You can determine the title and description for the front page by %sediting the front page itself &raquo;%s', 'ymbeseo' ), '<a href="' . esc_url( get_edit_post_link( get_option( 'page_on_front' ) ) ) . '">', '</a>' );
				echo '</p>';
				if ( get_option( 'page_for_posts' ) > 0 ) {
					echo '<p>', sprintf( __( 'You can determine the title and description for the blog page by %sediting the blog page itself &raquo;%s', 'ymbeseo' ), '<a href="' . esc_url( get_edit_post_link( get_option( 'page_for_posts' ) ) ) . '">', '</a>' ), '</p>';
				}
			}
			?>
		</div>
		<div id="post_types" class="ymbeseotab">
			<?php
			$post_types = get_post_types( array( 'public' => true ), 'objects' );
			if ( is_array( $post_types ) && $post_types !== array() ) {
				foreach ( $post_types as $pt ) {
					$warn = false;
					if ( $options['redirectattachment'] === true && $pt->name == 'attachment' ) {
						echo '<div class="ymbeseo-warning">';
						$warn = true;
					}

					$name = $pt->name;
					echo '<strong id="' . esc_attr( $name ) . '">' . esc_html( ucfirst( $pt->labels->name ) ) . '</strong><br/>';
					if ( $warn === true ) {
						echo '<h4 class="error-message">' . __( 'Take note:', 'ymbeseo' ) . '</h4>';

						echo '<p class="error-message">' . __( 'As you are redirecting attachment URLs to parent post URLs, these settings will currently only have an effect on <strong>unattached</strong> media items!', 'ymbeseo' ) . '</p>';
						echo '<p class="error-message">' . sprintf( __( 'So remember: If you change the %sattachment redirection setting%s in the future, the below settings will take effect for *all* media items.', 'ymbeseo' ), '<a href="' . esc_url( admin_url( 'admin.php?page=ymbeseo_advanced&tab=permalinks' ) ) . '">', '</a>' ) . '</p>';
					}

					$yform->textinput( 'title-' . $name, __( 'Title template', 'ymbeseo' ), 'template posttype-template' );
					$yform->textarea( 'metadesc-' . $name, __( 'Meta description template', 'ymbeseo' ), array( 'class' => 'template posttype-template' ) );
					if ( $options['usemetakeywords'] === true ) {
						$yform->textinput( 'metakey-' . $name, __( 'Meta keywords template', 'ymbeseo' ) );
					}
					$yform->checkbox( 'noindex-' . $name, '<code>noindex, follow</code>', __( 'Meta Robots', 'ymbeseo' ) );
					$yform->checkbox( 'showdate-' . $name, __( 'Show date in snippet preview?', 'ymbeseo' ), __( 'Date in Snippet Preview', 'ymbeseo' ) );
					/* translators: %1$s expands to Yoast SEO */
					$yform->checkbox( 'hideeditbox-' . $name, __( 'Hide', 'ymbeseo' ), sprintf( __( '%1$s Meta Box', 'ymbeseo' ), 'Yoast SEO' ) );

					/**
					 * Allow adding a custom checkboxes to the admin meta page - Post Types tab
					 * @api  YMBESEO_Admin_Pages  $yform  The YMBESEO_Admin_Pages object
					 * @api  String  $name  The post type name
					 */
					do_action( 'ymbeseo_admin_page_meta_post_types', $yform, $name );

					echo '<br/><br/>';
					if ( $warn === true ) {
						echo '</div>';
					}
					unset( $warn );
				}
				unset( $pt );
			}
			unset( $post_types );


			$post_types = get_post_types( array( '_builtin' => false, 'has_archive' => true ), 'objects' );
			if ( is_array( $post_types ) && $post_types !== array() ) {
				echo '<h2>' . __( 'Custom Post Type Archives', 'ymbeseo' ) . '</h2>';
				echo '<p>' . __( 'Note: instead of templates these are the actual titles and meta descriptions for these custom post type archive pages.', 'ymbeseo' ) . '</p>';

				foreach ( $post_types as $pt ) {
					$name = $pt->name;

					echo '<strong>' . esc_html( ucfirst( $pt->labels->name ) ) . '</strong><br/>';
					$yform->textinput( 'title-ptarchive-' . $name, __( 'Title', 'ymbeseo' ), 'template posttype-template' );
					$yform->textarea( 'metadesc-ptarchive-' . $name, __( 'Meta description', 'ymbeseo' ), array( 'class' => 'template posttype-template' ) );
					if ( $options['usemetakeywords'] === true ) {
						$yform->textinput( 'metakey-ptarchive-' . $name, __( 'Meta keywords', 'ymbeseo' ) );
					}
					if ( $options['breadcrumbs-enable'] === true ) {
						$yform->textinput( 'bctitle-ptarchive-' . $name, __( 'Breadcrumbs title', 'ymbeseo' ) );
					}
					$yform->checkbox( 'noindex-ptarchive-' . $name, '<code>noindex, follow</code>', __( 'Meta Robots', 'ymbeseo' ) );

					echo '<br/><br/>';
				}
				unset( $pt );
			}
			unset( $post_types );

			?>
		</div>
		<div id="taxonomies" class="ymbeseotab">
			<?php
			$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
			if ( is_array( $taxonomies ) && $taxonomies !== array() ) {
				foreach ( $taxonomies as $tax ) {
					echo '<strong>' . esc_html( ucfirst( $tax->labels->name ) ) . '</strong><br/>';
					$yform->textinput( 'title-tax-' . $tax->name, __( 'Title template', 'ymbeseo' ), 'template taxonomy-template' );
					$yform->textarea( 'metadesc-tax-' . $tax->name, __( 'Meta description template', 'ymbeseo' ), array( 'class' => 'template taxonomy-template' ) );
					if ( $options['usemetakeywords'] === true ) {
						$yform->textinput( 'metakey-tax-' . $tax->name, __( 'Meta keywords template', 'ymbeseo' ) );
					}
					$yform->checkbox( 'noindex-tax-' . $tax->name, '<code>noindex, follow</code>', __( 'Meta Robots', 'ymbeseo' ) );
					/* translators: %1$s expands to Yoast SEO */
					$yform->checkbox( 'hideeditbox-tax-' . $tax->name, __( 'Hide', 'ymbeseo' ), sprintf( __( '%1$s Meta Box', 'ymbeseo' ), 'Yoast SEO' ) );
					echo '<br/><br/>';
				}
				unset( $tax );
			}
			unset( $taxonomies );

			?>
		</div>
		<div id="archives" class="ymbeseotab">
			<?php
			echo '<h3>' . __( 'Author Archives', 'ymbeseo' ) . '</h3>';
			$yform->textinput( 'title-author-ymbeseo', __( 'Title template', 'ymbeseo' ), 'template author-template' );
			$yform->textarea( 'metadesc-author-ymbeseo', __( 'Meta description template', 'ymbeseo' ), array( 'class' => 'template author-template' ) );
			if ( $options['usemetakeywords'] === true ) {
				$yform->textinput( 'metakey-author-ymbeseo', __( 'Meta keywords template', 'ymbeseo' ) );
			}

			echo '<h3>' . __( 'Date Archives', 'ymbeseo' ) . '</h3>';
			$yform->textinput( 'title-archive-ymbeseo', __( 'Title template', 'ymbeseo' ), 'template date-template' );
			$yform->textarea( 'metadesc-archive-ymbeseo', __( 'Meta description template', 'ymbeseo' ), array( 'class' => 'template date-template' ) );
			echo '<br/>';

			echo '<h3>' . __( 'Duplicate content prevention', 'ymbeseo' ) . '</h3>';
			echo '<p>';
			/* translators: %1$s / %2$s: links to an article about duplicate content on yoast.com */
			printf( __( 'If you\'re running a one author blog, the author archive will be exactly the same as your homepage. This is what\'s called a %1$sduplicate content problem%2$s.', 'ymbeseo' ), '<a href="https://yoast.com/articles/duplicate-content/">', '</a>' );
			echo '<br />';
			/* translators: %s expands to <code>noindex, follow</code> */
			echo sprintf( __( 'If this is the case on your site, you can choose to either disable it (which makes it redirect to the homepage), or to add %s to it so it doesn\'t show up in the search results.', 'ymbeseo' ), '<code>noindex,follow</code>' );
			echo '</p>';
			/* translators: %s expands to <code>noindex, follow</code> */
			$yform->checkbox( 'noindex-author-ymbeseo', sprintf( __( 'Add %s to the author archives', 'ymbeseo' ), '<code>noindex, follow</code>' ) );
			$yform->checkbox( 'disable-author', __( 'Disable the author archives', 'ymbeseo' ) );
			echo '<p>';
			_e( 'Date-based archives could in some cases also be seen as duplicate content.', 'ymbeseo' );
			echo '</p>';
			/* translators: %s expands to <code>noindex, follow</code> */
			$yform->checkbox( 'noindex-archive-ymbeseo', sprintf( __( 'Add %s to the date-based archives', 'ymbeseo' ), '<code>noindex, follow</code>' ) );
			$yform->checkbox( 'disable-date', __( 'Disable the date-based archives', 'ymbeseo' ) );

			echo '<br/>';

			echo '<h2>' . __( 'Special Pages', 'ymbeseo' ) . '</h2>';
			/* translators: %s expands to <code>noindex, follow</code> */
			echo '<p>' . sprintf( __( 'These pages will be %s by default, so they will never show up in search results.', 'ymbeseo' ), '<code>noindex, follow</code>' ) . '</p>';
			echo '<p><strong>' . __( 'Search pages', 'ymbeseo' ) . '</strong><br/>';
			$yform->textinput( 'title-search-ymbeseo', __( 'Title template', 'ymbeseo' ), 'template search-template' );
			echo '</p>';
			echo '<p><strong>' . __( '404 pages', 'ymbeseo' ) . '</strong><br/>';
			$yform->textinput( 'title-404-ymbeseo', __( 'Title template', 'ymbeseo' ), 'template error404-template' );
			echo '</p>';
			echo '<br class="clear"/>';
			?>
		</div>
		<div id="other" class="ymbeseotab">
			<strong><?php _e( 'Sitewide meta settings', 'ymbeseo' ); ?></strong><br/>
			<br/>
			<?php
			echo '<p>', __( 'If you want to prevent /page/2/ and further of any archive to show up in the search results, enable this.', 'ymbeseo' ), '</p>';
			$yform->checkbox( 'noindex-subpages-ymbeseo', __( 'Noindex subpages of archives', 'ymbeseo' ) );

			echo '<p>', __( 'I don\'t know why you\'d want to use meta keywords, but if you want to, check this box.', 'ymbeseo' ), '</p>';
			$yform->checkbox( 'usemetakeywords', __( 'Use meta keywords tag?', 'ymbeseo' ) );

			echo '<p>', __( 'Prevents search engines from using the DMOZ description for pages from this site in the search results.', 'ymbeseo' ), '</p>';
			/* translators: %s expands to <code>noodp</code> */
			$yform->checkbox( 'noodp', sprintf( __( 'Add %s meta robots tag sitewide', 'ymbeseo' ), '<code>noodp</code>' ) );

			echo '<p>', __( 'Prevents search engines from using the Yahoo! directory description for pages from this site in the search results.', 'ymbeseo' ), '</p>';
			/* translators: %s expands to <code>noydir</code> */
			$yform->checkbox( 'noydir', sprintf( __( 'Add %s meta robots tag sitewide', 'ymbeseo' ), '<code>noydir</code>' ) );

			?>
		</div>

	</div>
<?php
$yform->admin_footer();
