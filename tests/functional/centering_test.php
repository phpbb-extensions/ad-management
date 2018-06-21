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
class centering_test extends functional_base
{
	/**
	 * {@inheritDoc}
	 */
	public function setUp()
	{
		parent::setUp();

		$this->disable_all_ads();
	}

	public function test_centering_enabled()
	{
		$ad_code = $this->create_ad('above_header', '', false, true);

		$crawler = self::request('GET', 'index.php');

		// Confirm above header ad is present with the phpbb-ads-center class
		$this->assertContains($ad_code, $crawler->html());
		$this->assertEquals(1, $crawler->filter('.phpbb-ads-center')->count());
	}

	public function test_centering_disabled()
	{
		// Make sure no ad with centering enabled is displayed
		$this->disable_all_ads();

		$ad_code = $this->create_ad('below_footer', '', false, false);

		$crawler = self::request('GET', 'index.php');

		// Confirm above header ad is present without the phpbb-ads-center class
		$this->assertContains($ad_code, $crawler->html());
		$this->assertEquals(0, $crawler->filter('.phpbb-ads-center')->count());
	}
}
