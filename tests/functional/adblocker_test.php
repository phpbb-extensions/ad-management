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
class adblocker_test extends functional_base
{
	public function test_adblocker_code_is_present()
	{
		// Enable an ad blocker message
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-ads-acp-main_module&mode=settings&sid=$this->sid");
		$form_data = [
			'adblocker_message'	=> 1,
		];
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, $form_data);
		$this->assertContainsLang('ACP_AD_SETTINGS_SAVED', $crawler->text());

		// Confirm ad blocker code is present
		$crawler = self::request('GET', 'index.php');
		self::assertEquals(1, $crawler->filter('#phpbb-aJHwDeoSqLhW')->count());
	}

	public function test_adblocker_code_is_not_present()
	{
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-ads-acp-main_module&mode=settings&sid=$this->sid");
		$form_data = [
			'adblocker_message'	=> 0,
		];
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, $form_data);
		$this->assertContainsLang('ACP_AD_SETTINGS_SAVED', $crawler->text());

		$crawler = self::request('GET', 'index.php');
		self::assertEquals(0, $crawler->filter('#phpbb-aJHwDeoSqLhW')->count());
	}
}
