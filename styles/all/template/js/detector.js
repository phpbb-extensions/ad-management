(function(window, document) {
	'use strict';

	const baitClasses = [
		'pub_300x250',
		'pub_300x250m',
		'pub_728x90',
		'text-ad',
		'textAd',
		'text_ad',
		'text_ads',
		'text-ads',
		'text-ad-links',
		'ad-text',
		'adSense',
		'adsbygoogle',
		'adBlock',
		'adContent',
		'adBanner'
	];

	function createBait() {
		const bait = document.createElement('div');

		bait.className = baitClasses.join(' ');
		bait.setAttribute('aria-hidden', 'true');
		bait.style.cssText = 'width:1px!important;height:1px!important;position:absolute!important;left:-10000px!important;top:-1000px!important;';

		return bait;
	}

	function isHidden(element) {
		if (!element) {
			return true;
		}

		if (
			element.offsetParent === null ||
			element.offsetHeight === 0 ||
			element.offsetWidth === 0 ||
			element.clientHeight === 0 ||
			element.clientWidth === 0
		) {
			return true;
		}

		if (window.getComputedStyle) {
			const style = window.getComputedStyle(element);

			if (style && (style.getPropertyValue('display') === 'none' || style.getPropertyValue('visibility') === 'hidden')) {
				return true;
			}
		}

		return false;
	}

	function detectDomBait() {
		return new Promise((resolve) => {
			if (!document.body) {
				resolve(false);
				return;
			}

			const bait = createBait();
			document.body.appendChild(bait);

			window.setTimeout(() => {
				const blocked = isHidden(bait);

				if (bait.parentNode) {
					bait.parentNode.removeChild(bait);
				}

				resolve(blocked);
			}, 50);
		});
	}

	function detectBaitRequest(url) {
		if (!url || !window.fetch) {
			return Promise.resolve(false);
		}

		return window.fetch(url, {
			cache: 'no-store',
			credentials: 'same-origin'
		}).then((response) => !response.ok)
			.catch(() => true);
	}

	window.phpbbAdsDetectAdblock = {
		detect(options) {
			const settings = options || {};

			return Promise.all([
				detectDomBait(),
				detectBaitRequest(settings.baitUrl)
			]).then(([domBlocked, requestBlocked]) => domBlocked || requestBlocked);
		}
	};
})(window, document);
