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
class hide_group_test extends location_base
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

		// Reset hide groups
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-admanagement-acp-main_module&mode=settings&sid={$this->sid}");
		$form_data = array(
			'hide_groups'	=> array(),
		);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, $form_data);
	}

	public function test_ad_displays_without_hide_group()
	{
		$ad_code = $this->create_ad('above_header');

		$crawler = self::request('GET', "index.php");

		// Confirm above header ad is present
		$this->assertContains($ad_code, $crawler->html());
	}

	public function test_ad_hides_with_hide_group()
	{
		// Hide ads for administratos
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-admanagement-acp-main_module&mode=settings&sid={$this->sid}");
		$form_data = array(
			'hide_groups'	=> array(5),
		);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, $form_data);

		$ad_code = $this->create_ad('below_footer');

		$crawler = self::request('GET', "index.php");

		// Confirm above header ad is not present
		$this->assertNotContains($ad_code, $crawler->html());
	}
}
