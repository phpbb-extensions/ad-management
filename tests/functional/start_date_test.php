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
class start_date_test extends functional_base
{
	/**
	* {@inheritDoc}
	*/
	public function setUp()
	{
		parent::setUp();

		$this->disable_all_ads();
	}

	public function test_no_start_date_displays()
	{
		$ad_code = $this->create_ad('above_header');

		$crawler = self::request('GET', 'index.php');

		// Confirm above header ad is present
		$this->assertContains($ad_code, $crawler->html());
	}

	public function test_past_start_date_displays()
	{
		$ad_code = $this->create_ad('above_footer', '', false, false, '2030-01-01');

		// Change the ads start date to a time long ago
		$sql = 'UPDATE phpbb_ads
			SET ad_start_date = ' . strtotime('2018-01-01');
		$this->db->sql_query($sql);

		$crawler = self::request('GET', 'index.php');

		// Confirm above header ad is present
		$this->assertContains($ad_code, $crawler->html());

		return $ad_code;
	}

	public function test_future_start_date_is_not_displayed()
	{
		$ad_code = $this->create_ad('below_header', '', false, false, '2035-01-01');

		$crawler = self::request('GET', 'index.php');

		// Confirm below header ad is not present
		$this->assertNotContains($ad_code, $crawler->html());
	}
}
