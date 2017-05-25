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
class admin_controller_test extends \phpbb_functional_test_case
{
	/**
	* {@inheritDoc}
	*/
	static protected function setup_extensions()
	{
		return array('phpbb/admanagement');
	}

	/**
	* {@inheritDoc}
	*/
	public function setUp()
	{
		parent::setUp();

		$this->add_lang_ext('phpbb/admanagement', array(
			'info_acp_admanagement',
			'acp',
		));

		$this->login();
		$this->admin_login();
	}

	/**
	* Test that Advertisement management ACP module appears
	*/
	public function test_acp_module()
	{
		// Load Advertisement management ACP page
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-admanagement-acp-main_module&mode=manage&sid={$this->sid}");

		// Assert Advertisement management module appears in sidebar
		$this->assertContainsLang('ACP_ADMANAGEMENT_TITLE', $crawler->filter('.menu-block')->text());
		$this->assertContainsLang('ACP_MANAGE_ADS_TITLE', $crawler->filter('#activemenu')->text());

		// Assert Advertisement management display appears
		$this->assertContainsLang('ACP_ADS_EMPTY', $crawler->filter('#main')->text());
		$this->assertContainsLang('ACP_MANAGE_ADS_TITLE', $crawler->filter('#main')->text());
		$this->assertContainsLang('ACP_ADS_ADD', $crawler->filter('input.button2')->attr('value'));
	}
}
