(function(window, document) {
	'use strict';

	var clickCooldown = 10000;
	var tracked = {};
	var observed = [];

	function closest(element, predicate) {
		while (element && element !== document) {
			if (element.nodeType === 1 && predicate(element)) {
				return element;
			}
			element = element.parentElement;
		}

		return null;
	}

	function hasBox(element) {
		var rect = element.getBoundingClientRect();
		return rect.width > 0 && rect.height > 0 && element.getClientRects().length > 0;
	}

	function isDisplayed(element) {
		while (element && element.nodeType === 1) {
			var style = window.getComputedStyle ? window.getComputedStyle(element) : element.currentStyle;
			if (style && (style.display === 'none' || style.visibility === 'hidden' ||
				style.visibility === 'collapse' || parseFloat(style.opacity) === 0)) {
				return false;
			}
			element = element.parentElement;
		}

		return true;
	}

	function hasText(element) {
		var childNodes = element.childNodes;
		for (var i = 0; i < childNodes.length; i++) {
			if (childNodes[i].nodeType === 3 && childNodes[i].nodeValue.replace(/\s/g, '')) {
				return true;
			}
		}

		return false;
	}

	function hasVisibleContent(ad) {
		if (!document.documentElement.contains(ad) || !isDisplayed(ad) || !hasBox(ad)) {
			return false;
		}
		if (hasText(ad)) {
			return true;
		}

		var elements = ad.querySelectorAll('*');
		for (var i = 0; i < elements.length; i++) {
			var element = elements[i];
			var tag = element.tagName;
			if (/^(SCRIPT|STYLE|LINK|META|NOSCRIPT|TEMPLATE)$/.test(tag) ||
				!isDisplayed(element) || !hasBox(element)) {
				continue;
			}

			if (tag === 'IMG') {
				if (element.complete && element.naturalWidth > 0 && element.naturalHeight > 0) {
					return true;
				}
				continue;
			}

			if (/^(IFRAME|VIDEO|CANVAS|SVG|OBJECT|EMBED)$/.test(tag)) {
				return true;
			}

			var style = window.getComputedStyle ? window.getComputedStyle(element) : element.currentStyle;
			var background = style ? style.backgroundColor : '';
			var hasBackground = style && (style.backgroundImage !== 'none' || (background !== 'transparent' &&
				background !== 'rgba(0, 0, 0, 0)' && !/rgba\([^)]*,\s*0(?:\.0+)?\)$/.test(background)));
			if (hasText(element) || hasBackground) {
				return true;
			}
		}

		return false;
	}

	function post(url) {
		if (window.fetch) {
			window.fetch(url, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {'X-Requested-With': 'XMLHttpRequest'},
				keepalive: true
			}).catch(function() {});
			return;
		}

		var request = new window.XMLHttpRequest();
		request.open('POST', url, true);
		request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		request.send();
	}

	function track(ad) {
		var id = ad.getAttribute('data-phpbb-ads-id');
		var rect = ad.getBoundingClientRect();
		var inViewport = rect.bottom > 0 && rect.right > 0 && rect.top < window.innerHeight && rect.left < window.innerWidth;
		if (!id || tracked[id] || !inViewport || !hasVisibleContent(ad)) {
			return;
		}

		tracked[id] = true;
		post(ad.getAttribute('data-phpbb-ads-view-url'));
	}

	function observeAds() {
		var ads = document.querySelectorAll('[data-phpbb-ads-view-url]');
		for (var i = 0; i < ads.length; i++) {
			track(ads[i]);
			if (observed.indexOf(ads[i]) !== -1) {
				continue;
			}
			observed.push(ads[i]);
			if (window.IntersectionObserver) {
				observer.observe(ads[i]);
			} else {
				track(ads[i]);
			}
		}
	}

	var observer = window.IntersectionObserver ? new window.IntersectionObserver(function(entries) {
		for (var i = 0; i < entries.length; i++) {
			if (entries[i].isIntersecting) {
				track(entries[i].target);
			}
		}
	}) : null;

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
			if (now - parseInt(window.sessionStorage.getItem(key), 10) < clickCooldown) {
				return;
			}
			window.sessionStorage.setItem(key, now.toString());
		} catch (error) {
			// Storage may be unavailable; server-side cooldown still applies.
		}

		post(ad.getAttribute('data-phpbb-ads-click-url'));
	});

	window.addEventListener('load', function() {
		observeAds();
		if (window.MutationObserver) {
			new window.MutationObserver(observeAds).observe(document.body, {childList: true, subtree: true});
		}
		document.addEventListener('load', observeAds, true);
		if (!window.IntersectionObserver) {
			window.addEventListener('scroll', observeAds);
			window.addEventListener('resize', observeAds);
		}
	});
})(window, document);
