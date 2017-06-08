<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\admanagement\tests\functional;

/**
* @group functional
*/
class end_date_test extends location_base
{
	/**
	* {@inheritDoc}
	*/
	public function setUp()
	{
		parent::setUp();

		// Disable all existent ads
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-admanagement-acp-main_module&mode=manage&sid={$this->sid}");
		while (count($crawler->selectLink($this->lang('ENABLED'))))
		{
			$disable_link = $crawler->selectLink($this->lang('ENABLED'))->link();
			self::$client->click($disable_link);
			$crawler = self::request('GET', "adm/index.php?i=-phpbb-admanagement-acp-main_module&mode=manage&sid={$this->sid}");
		}
	}

	public function test_no_end_date_displays()
	{
		$ad_code = $this->create_ad('above_header');

		$crawler = self::request('GET', "index.php");

		// Confirm above header ad is present
		$this->assertContains($ad_code, $crawler->html());
	}

	public function test_future_end_date_displays()
	{
		$ad_code = $this->create_ad('above_footer', '2035-01-01');

		$crawler = self::request('GET', "index.php");

		// Confirm above header ad is present
		$this->assertContains($ad_code, $crawler->html());
	}

	public function test_past_end_date_is_not_displayed()
	{
		$ad_code = $this->create_ad('below_footer', '2000-01-01');

		$crawler = self::request('GET', "index.php");

		// Confirm above header ad is present
		$this->assertNotContains($ad_code, $crawler->html());
	}
}
