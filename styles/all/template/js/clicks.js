$(function() {
	'use strict';

	$('[data-ads-id]').on('click', function(e) {
		$.get(u_phpbb_ads_click.replace(/0$/, $(this).attr('data-ads-id')));
	});
});
