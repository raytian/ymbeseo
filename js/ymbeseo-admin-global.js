/* global ajaxurl */
/* jshint -W097 */
/* jshint unused:false */
'use strict';

/**
 * Used to dismiss the tagline notice for a specific user.
 *
 * @param {string} nonce
 */
function ymbeseoDismissTaglineNotice( nonce ) {
	jQuery.post( ajaxurl, {
			action: 'ymbeseo_dismiss_tagline_notice',
			_wpnonce: nonce
		}
	);
}

/**
 * Used to remove the admin notices for several purposes, dies on exit.
 *
 * @param {string} option
 * @param {string} hide
 * @param {string} nonce
 */
function ymbeseoSetIgnore( option, hide, nonce ) {
	jQuery.post( ajaxurl, {
			action: 'ymbeseo_set_ignore',
			option: option,
			_wpnonce: nonce
		}, function( data ) {
			if ( data ) {
				jQuery( '#' + hide ).hide();
				jQuery( '#hidden_ignore_' + option ).val( 'ignore' );
			}
		}
	);
}

/**
 * Make the notices dismissible (again)
 */
function ymbeseoMakeDismissible() {
	jQuery( '.notice.is-dismissible' ).each( function() {
		var $notice = jQuery( this );
		if ( $notice.find( '.notice-dismiss').empty() ) {
			var	$button = jQuery( '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>' );

			$notice.append( $button );

			$button.on( 'click.wp-dismiss-notice', function( event ) {
				event.preventDefault();
				$notice.fadeTo( 100 , 0, function() {
					jQuery(this).slideUp( 100, function() {
						jQuery(this).remove();
					});
				});
			});
		}
	});
}

jQuery( document ).ready( function() {
	jQuery( '#ymbeseo-dismiss-about > .notice-dismiss' ).click( function() {
		ymbeseoDismissAbout( jQuery( '#ymbeseo-dismiss-about' ).data( 'nonce' ) );
	});

	jQuery( '#ymbeseo-dismiss-tagline-notice > .notice-dismiss').click( function() {
		ymbeseoDismissTaglineNotice( jQuery( '#ymbeseo-dismiss-tagline-notice').data( 'nonce' ) );
	});

	jQuery( '.yoast-dismissible > .notice-dismiss').click( function() {
		var parent_div = jQuery( this ).parent('.yoast-dismissible');

		jQuery.post(
			ajaxurl,
			{
				action: parent_div.attr( 'id').replace( /-/g, '_' ),
				_wpnonce: parent_div.data( 'nonce' ),
				data: parent_div.data( 'json' )
			}
		);
	});
});
