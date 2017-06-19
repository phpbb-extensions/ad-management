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
class priority_test extends functional_base
{
	/**
	* {@inheritDoc}
	*/
	public function setUp()
	{
		parent::setUp();

		// Disable all existent ads
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-ads-acp-main_module&mode=manage&sid={$this->sid}");
		while (count($crawler->selectLink($this->lang('ENABLED'))))
		{
			$disable_link = $crawler->selectLink($this->lang('ENABLED'))->link();
			self::$client->click($disable_link);
			$crawler = self::request('GET', "adm/index.php?i=-phpbb-ads-acp-main_module&mode=manage&sid={$this->sid}");
		}
	}

	public function test_ad_priority()
	{
		$this->create_ad('above_header', '', 1);
		$ad_code = $this->create_ad('above_header', '', 2);

		$crawler = self::request('GET', 'index.php');

		// Confirm above header ad is present
		$this->assertContains($ad_code, $crawler->html());

		$crawler = self::request('GET', 'index.php');

		// Confirm that it really always favors higher priority
		$this->assertContains($ad_code, $crawler->html());
	}
}
