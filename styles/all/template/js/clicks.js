(function($, u_phpbb_ads_click) {
	'use strict';

	$(function() {
		$('[data-phpbb-ads-id]').on('click', 'a', function(e) {
			var url = u_phpbb_ads_click.replace(/(?:0\?sid=.+|0)$/, $(e.delegateTarget).attr('data-phpbb-ads-id'));
			console.log(url);
			$.get(url);
		});
	});
})(jQuery, u_phpbb_ads_click);
