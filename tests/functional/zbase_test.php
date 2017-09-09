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
class zbase_test extends \phpbb_functional_test_case
{
	/**
	 * {@inheritDoc}
	 */
	protected static function setup_extensions()
	{
		return array('phpbb/ads');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setUp()
	{
		parent::setUp();

		$this->add_lang('acp/extensions');

		$this->login();
		$this->admin_login();
	}

	public function test_disable_delete()
	{
		// Disable
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=disable_pre&ext_name=phpbb%2Fads&sid=' . $this->sid);
		$this->assertContains($this->lang('EXTENSION_DISABLE_CONFIRM', 'Advertisement Management'), $crawler->filter('#main')->text());
		$form = $crawler->selectButton($this->lang('EXTENSION_DISABLE'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('EXTENSION_DISABLE_SUCCESS', $crawler->filter('.successbox')->text());

		// Delete
		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=delete_data_pre&ext_name=phpbb%2Fads&sid=' . $this->sid);
		$this->assertContains($this->lang('EXTENSION_DELETE_DATA_CONFIRM', 'Advertisement Management'), static::get_content()); // use get_content because lang contains HTML
		$form = $crawler->selectButton($this->lang('EXTENSION_DELETE_DATA'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('EXTENSION_DELETE_DATA_SUCCESS', $crawler->filter('.successbox')->text());
	}
}
