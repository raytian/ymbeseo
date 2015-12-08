/* jshint unused:false */
/* global ajaxurl */
/* global tb_remove */
jQuery( function() {
	'use strict';

	jQuery('#gsc_auth_code').click(
		function() {
			var auth_url = jQuery('#gsc_auth_url').val(),
			    w = 600,
				h = 500,
				left = (screen.width / 2) - (w / 2),
				top = (screen.height / 2) - (h / 2);
			return window.open(auth_url, 'ymbeseogscauthcode', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
		}
	);
});

function ymbeseo_update_category_count(category) {
	'use strict';

	var count_element = jQuery('#gsc_count_' + category + '');
	var new_count     = parseInt( count_element.text() , 10) - 1;
	if(new_count < 0) {
		new_count = 0;
	}

	count_element.text(new_count);
}

function ymbeseo_mark_as_fixed(url) {
	'use strict';

	jQuery.post(
		ajaxurl,
		{
			action: 'ymbeseo_mark_fixed_crawl_issue',
			ajax_nonce: jQuery('.ymbeseo-gsc-ajax-security').val(),
			platform: jQuery('#field_platform').val(),
			category: jQuery('#field_category').val(),
			url: url
		},
		function(response) {
			if ('true' === response) {
				ymbeseo_update_category_count(jQuery('#field_category').val());
				jQuery('span:contains(' + url + ')').closest('tr').remove();
			}
		}
	);
}

jQuery( document ).ready( function() {
	'use strict';
	jQuery('a.gsc_category').qtip(
		{
			content: {
				attr: 'title'
			},
			position: {
				my: 'bottom left',
				at: 'top center'
			},
			style: {
				tip: {
					corner: true
				},
				classes: 'yoast-qtip qtip-rounded qtip-blue'
			},
			show: 'mouseenter',
			hide: {
				fixed: true,
				delay: 500
			}
		}
	);
});
