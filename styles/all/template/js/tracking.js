(function(window, document) {
	'use strict';

	const clickCooldown = 10000;
	const tracked = {};
	const observed = [];

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
		const rect = element.getBoundingClientRect();
		return rect.width > 0 && rect.height > 0 && element.getClientRects().length > 0;
	}

	function isDisplayed(element) {
		while (element && element.nodeType === 1) {
			const style = window.getComputedStyle ? window.getComputedStyle(element) : element.currentStyle;
			if (style && (style.display === 'none' || style.visibility === 'hidden' ||
				style.visibility === 'collapse' || parseFloat(style.opacity) === 0)) {
				return false;
			}
			element = element.parentElement;
		}

		return true;
	}

	function hasText(element) {
		for (const node of element.childNodes) {
			if (node.nodeType === 3 && node.nodeValue.replace(/\s/g, '')) {
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

		const elements = ad.querySelectorAll('*');
		for (const element of elements) {
			const tag = element.tagName;
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

			const style = window.getComputedStyle ? window.getComputedStyle(element) : element.currentStyle;
			const background = style ? style.backgroundColor : '';
			const hasBackground = style && (style.backgroundImage !== 'none' || (background !== 'transparent' &&
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
			}).catch(() => {});
			return;
		}

		const request = new window.XMLHttpRequest();
		request.open('POST', url, true);
		request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		request.send();
	}

	function track(ad) {
		const id = ad.getAttribute('data-phpbb-ads-id');
		const rect = ad.getBoundingClientRect();
		const inViewport = rect.bottom > 0 && rect.right > 0 && rect.top < window.innerHeight && rect.left < window.innerWidth;
		if (!id || tracked.has(id) || !inViewport || !hasVisibleContent(ad)) {
			return;
		}

		tracked.add(id);
		post(ad.getAttribute('data-phpbb-ads-view-url'));
	}

	function observeAds() {
		const ads = document.querySelectorAll('[data-phpbb-ads-view-url]');
		for (const ad of ads) {
			track(ad);
			if (observed.has(ad)) {
				continue;
			}
			observed.add(ad);
			if (window.IntersectionObserver) {
				observer.observe(ad);
			}
		}
	}

	const observer = window.IntersectionObserver ? new window.IntersectionObserver((entries) => {
		for (const entry of entries) {
			if (entry.isIntersecting) {
				track(entry.target);
			}
		}
	}) : null;

	document.addEventListener('click', (event) => {
		const link = closest(event.target, (element) => element.tagName === 'A');
		const ad = closest(link, (element) => element.hasAttribute('data-phpbb-ads-click-url'));
		if (!ad) {
			return;
		}

		const key = 'phpbb_ads_click_' + ad.getAttribute('data-phpbb-ads-id');
		const now = Date.now();

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

	window.addEventListener('load', () => {
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
