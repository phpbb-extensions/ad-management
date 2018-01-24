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
class content_only_test extends functional_base
{
	/**
	 * {@inheritDoc}
	 */
	public function setUp()
	{
		parent::setUp();

		$this->disable_all_ads();
	}

	public function test_content_only_ad_displays()
	{
		$ad_code = $this->create_ad('above_header', '', true);

		$crawler = self::request('GET', 'index.php');

		// Confirm above header ad is present
		$this->assertContains($ad_code, $crawler->html());
	}

	public function test_content_only_ad_hides()
	{
		$ad_code = $this->create_ad('above_header', '', true);

		$crawler = self::request('GET', 'ucp.php');
		$this->assertNotContains($ad_code, $crawler->html());

		$crawler = self::request('GET', 'mcp.php');
		$this->assertNotContains($ad_code, $crawler->html());
	}
}
