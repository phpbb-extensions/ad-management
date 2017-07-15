(function ($, u_phpbb_ads_click) {
	'use strict';

	$(function() {
		$('[data-ad-id]').on('click', 'a', function (e) {
			$.get(u_phpbb_ads_click.replace(/0$/, $(e.delegateTarget).attr('data-ad-id')));
		});
	});
})(jQuery, u_phpbb_ads_click);
