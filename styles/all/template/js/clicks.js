(function($, u_phpbb_ads_click) {
	'use strict';

	$(function() {
		$('[data-phpbb-ads-id]').on('click', 'a', function(e) {
			$.get(u_phpbb_ads_click.replace(/(?:0\?sid=.+|0)$/, $(e.delegateTarget).attr('data-phpbb-ads-id')));
		});
	});
})(jQuery, u_phpbb_ads_click);
