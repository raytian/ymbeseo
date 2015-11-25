<?php
/**
 * @package YMBESEO\Admin
 */

?>

<h3 id="wordpress-seo"><?php
	/* translators: %1$s expands to Yoast SEO */
	printf( __( '%1$s settings', 'ymbeseo' ), 'Yoast SEO' );
	?></h3>
<table class="form-table">
	<tr>
		<th>
			<label
				for="YMBESEO_author_title"><?php _e( 'Title to use for Author page', 'ymbeseo' ); ?></label>
		</th>
		<td><input class="regular-text" type="text" id="YMBESEO_author_title" name="YMBESEO_author_title"
		           value="<?php echo esc_attr( get_the_author_meta( 'YMBESEO_title', $user->ID ) ); ?>"/>
		</td>
	</tr>
	<tr>
		<th>
			<label
				for="YMBESEO_author_metadesc"><?php _e( 'Meta description to use for Author page', 'ymbeseo' ); ?></label>
		</th>
		<td>
						<textarea rows="3" cols="30" id="YMBESEO_author_metadesc"
						          name="YMBESEO_author_metadesc"><?php echo esc_textarea( get_the_author_meta( 'YMBESEO_metadesc', $user->ID ) ); ?></textarea>
		</td>
	</tr>
	<?php if ( $options['usemetakeywords'] === true ) { ?>
		<tr>
			<th>
				<label
					for="YMBESEO_author_metakey"><?php _e( 'Meta keywords to use for Author page', 'ymbeseo' ); ?></label>
			</th>
			<td>
				<input class="regular-text" type="text" id="YMBESEO_author_metakey"
				       name="YMBESEO_author_metakey"
				       value="<?php echo esc_attr( get_the_author_meta( 'YMBESEO_metakey', $user->ID ) ); ?>"/>
			</td>
		</tr>
	<?php } ?>
	<tr>
		<th>
			<label
				for="YMBESEO_author_exclude"><?php _e( 'Exclude user from Author-sitemap', 'ymbeseo' ); ?></label>
		</th>
		<td>
			<input class="checkbox double" type="checkbox" id="YMBESEO_author_exclude"
			       name="YMBESEO_author_exclude"
			       value="on" <?php echo ( get_the_author_meta( 'YMBESEO_excludeauthorsitemap', $user->ID ) === 'on' ) ? 'checked' : ''; ?> />
		</td>
	</tr>
</table>
<br/><br/>
