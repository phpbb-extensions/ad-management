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
class acp_settings_test extends functional_base
{
	/**
	* Test Advertisement management ACP settings
	*/
	public function test_acp_settings()
	{
		// Load Advertisement management ACP page
		$crawler = $this->get_settings_page();

		// Confirm page contains proper heading
		$this->assertContainsLang('SETTINGS', $crawler->text());

		// Confirm no group is selected yet
		$this->assertCount(0, $crawler->filter('option[selected]'));

		// Submit form
		$form_data = array(
			'adblocker_message'	=> 1,
			'enable_views'		=> 1,
			'enable_clicks'		=> 1,
		);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, $form_data);
		$this->assertContainsLang('ACP_AD_SETTINGS_SAVED', $crawler->text());

		// Load Advertisement management ACP page again
		$crawler = $this->get_settings_page();

		// Confirm Adblocker, views and clicks are enabled and admin group is selected
		$this->assertEquals('1', $crawler->filter('input[name="adblocker_message"][checked]')->attr('value'));
		$this->assertEquals('1', $crawler->filter('input[name="enable_views"][checked]')->attr('value'));
		$this->assertEquals('1', $crawler->filter('input[name="enable_clicks"][checked]')->attr('value'));
	}

	protected function get_settings_page()
	{
		return self::request('GET', "adm/index.php?i=-phpbb-ads-acp-main_module&mode=settings&sid={$this->sid}");
	}
}
