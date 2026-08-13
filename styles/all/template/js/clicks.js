(function(window, document) {
	'use strict';

	var cooldown = 10000;

	function closest(element, predicate) {
		while (element && element !== document) {
			if (element.nodeType === 1 && predicate(element)) {
				return element;
			}
			element = element.parentElement;
		}

		return null;
	}

	function post(url) {
		if (window.fetch) {
			window.fetch(url, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
				keepalive: true
			}).catch(function() {});
			return;
		}

		var request = new window.XMLHttpRequest();
		request.open('POST', url, true);
		request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		request.send();
	}

	document.addEventListener('click', function(event) {
		var link = closest(event.target, function(element) {
			return element.tagName === 'A';
		});
		var ad = closest(link, function(element) {
			return element.hasAttribute('data-phpbb-ads-click-url');
		});
		if (!ad) {
			return;
		}

		var key = 'phpbb_ads_click_' + ad.getAttribute('data-phpbb-ads-id');
		var now = Date.now();

		try {
			if (now - parseInt(window.sessionStorage.getItem(key), 10) < cooldown) {
				return;
			}
			window.sessionStorage.setItem(key, now.toString());
		} catch (error) {
			// Storage may be unavailable; server-side cooldown still applies.
		}

		post(ad.getAttribute('data-phpbb-ads-click-url'));
	});
})(window, document);
