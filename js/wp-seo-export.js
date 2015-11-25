/* jshint strict:true */
/* global ajaxurl */
/* global YMBESEO_export_nonce */
jQuery( document ).ready( function( $ ) {
		'use strict';
		$( '#export-button' ).click( function() {
				$.post( ajaxurl, {
						action: 'YMBESEO_export',
						_wpnonce: YMBESEO_export_nonce,
						include_taxonomy: $( '#include_taxonomy_meta' ).is( ':checked' )
					}, function( resp ) {
						resp = JSON.parse( resp );
						var dclass = 'error';
						if ( resp.status === 'success' ) {
							dclass = 'updated';
						}
						$( '#wpseo-title' ).append( '<div class="' + dclass + ' settings-error"><p><strong>' + resp.msg + '</strong></p></div>' );
					}
				);
				event.preventDefault();
			}
		);
	}
);
