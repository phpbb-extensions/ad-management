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
class visual_demo_test extends functional_base
{
	public function test_visual_demo()
	{
		// Load Advertisement management ACP page
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-ads-acp-main_module&mode=manage&sid={$this->sid}");

		// Jump to the add page
		$form = $crawler->selectButton($this->lang('ACP_ADS_ADD'))->form();
		$crawler = self::submit($form);

		// Hit "Start visual demo of ad locations" button
		$visual_demo_link = $crawler->selectLink('Start visual demo of ad locations')->link();
		$crawler = self::$client->click($visual_demo_link);

		// We should be on index page now. Visual demo disable prompt should be displayed.
		$this->assertContainsLang('DISABLE_VISUAL_DEMO', $crawler->filter('.rules')->text());
	}
}
