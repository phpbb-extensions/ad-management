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
class end_date_test extends functional_base
{
	/**
	* {@inheritDoc}
	*/
	public function setUp()
	{
		parent::setUp();

		$this->disable_all_ads();
	}

	public function test_no_end_date_displays()
	{
		$ad_code = $this->create_ad('above_header');

		$crawler = self::request('GET', 'index.php');

		// Confirm above header ad is present
		$this->assertContains($ad_code, $crawler->html());
	}

	public function test_future_end_date_displays()
	{
		$ad_code = $this->create_ad('above_footer', '2035-01-01');

		$crawler = self::request('GET', 'index.php');

		// Confirm above header ad is present
		$this->assertContains($ad_code, $crawler->html());

		return $ad_code;
	}

	/**
	 * @depends test_future_end_date_displays
	 */
	public function test_past_end_date_is_not_displayed($ad_code)
	{
		// Change the ads end date to a time long ago
		$sql = 'UPDATE phpbb_ads
			SET ad_end_date = ' . strtotime('2000-01-01') . '
			WHERE ad_end_date = ' . strtotime('2035-01-01');
		$this->db->sql_query($sql);

		$crawler = self::request('GET', 'index.php');

		// Confirm above header ad is present
		$this->assertNotContains($ad_code, $crawler->html());
	}
}
