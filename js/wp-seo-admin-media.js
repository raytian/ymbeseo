/* global ymbeseoMediaL10n */
/* global ajaxurl */
/* global wp */
/* jshint -W097 */
/* jshint -W003 */
/* jshint unused:false */
'use strict';
// Taken and adapted from http://www.webmaster-source.com/2013/02/06/using-the-wordpress-3-5-media-uploader-in-your-plugin-or-theme/
jQuery( document ).ready( function( $ ) {
		var ymbeseo_custom_uploader;
		$( '.ymbeseo_image_upload_button' ).click( function( e ) {
				var ymbeseo_target_id = $( this ).attr( 'id' ).replace( /_button$/, '' );
				e.preventDefault();
				if ( ymbeseo_custom_uploader ) {
					ymbeseo_custom_uploader.open();
					return;
				}
				ymbeseo_custom_uploader = wp.media.frames.file_frame = wp.media( {
						title: ymbeseoMediaL10n.choose_image,
						button: { text: ymbeseoMediaL10n.choose_image },
						multiple: false
					}
				);
				ymbeseo_custom_uploader.on( 'select', function() {
						var attachment = ymbeseo_custom_uploader.state().get( 'selection' ).first().toJSON();
						$( '#' + ymbeseo_target_id ).val( attachment.url );
					}
				);
				ymbeseo_custom_uploader.open();
			}
		);
	}
);
