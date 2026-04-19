<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\functional;

/**
 * @group functional
 */
class ads_consentmanager_test extends functional_base
{
	protected static function setup_extensions()
	{
		return array('phpbb/consentmanager', 'phpbb/ads');
	}

	public function test_ads_are_deferred_until_marketing_consent_exists()
	{
		$this->create_ad(
			'above_header',
			'',
			false,
			true,
			'',
			'<div class="ad-snippet">Example ad</div><script src="https://ads.example.com/tag.js"></script><iframe src="https://ads.example.com/frame"></iframe>'
		);

		$crawler = self::request('GET', 'index.php');

		self::assertSame(1, $crawler->filter('.phpbb-ads script[type="text/plain"][data-consent-category="marketing"][src*="ads.example.com/tag.js"]')->count());
		self::assertSame(0, $crawler->filter('.phpbb-ads script[src*="ads.example.com/tag.js"]:not([type="text/plain"])')->count());
		self::assertSame(1, $crawler->filter('.phpbb-ads iframe[src*="ads.example.com/frame"]')->count());
	}
}
