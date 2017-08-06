<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\functional;

/**
 * @group functional
 */
class location_after_footer_navbar_test extends functional_base
{
	public function test_location_after_footer_navbar()
	{
		$ad_code = $this->create_ad('after_footer_navbar');

		$crawler = self::request('GET', 'index.php');

		// Confirm after footer navbar ad is present on correct location
		$this->assertContains($ad_code, $crawler->filter('.copyright')->html());
	}
}
