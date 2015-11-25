/* global wpseoMediaL10n */
/* global ajaxurl */
/* global wp */
/* jshint -W097 */
/* jshint -W003 */
/* jshint unused:false */
'use strict';
// Taken and adapted from http://www.webmaster-source.com/2013/02/06/using-the-wordpress-3-5-media-uploader-in-your-plugin-or-theme/
jQuery( document ).ready( function( $ ) {
		var YMBESEO_custom_uploader;
		$( '.YMBESEO_image_upload_button' ).click( function( e ) {
				var YMBESEO_target_id = $( this ).attr( 'id' ).replace( /_button$/, '' );
				e.preventDefault();
				if ( YMBESEO_custom_uploader ) {
					YMBESEO_custom_uploader.open();
					return;
				}
				YMBESEO_custom_uploader = wp.media.frames.file_frame = wp.media( {
						title: wpseoMediaL10n.choose_image,
						button: { text: wpseoMediaL10n.choose_image },
						multiple: false
					}
				);
				YMBESEO_custom_uploader.on( 'select', function() {
						var attachment = YMBESEO_custom_uploader.state().get( 'selection' ).first().toJSON();
						$( '#' + YMBESEO_target_id ).val( attachment.url );
					}
				);
				YMBESEO_custom_uploader.open();
			}
		);
	}
);
