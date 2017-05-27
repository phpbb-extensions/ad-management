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
class acp_test extends \phpbb_functional_test_case
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

	/**
	* Test Advertisement management ACP add page
	*/
	public function test_acp_add()
	{
		// Load Advertisement management ACP page
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-admanagement-acp-main_module&mode=manage&sid={$this->sid}");

		// Jump to the add page
		$form = $crawler->selectButton($this->lang('ACP_ADS_ADD'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('ACP_ADS_ADD', $crawler->filter('#main h1')->text());

		// Confirm error when submitting without required field data
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form);
		$this->assertGreaterThan(0, $crawler->filter('.errorbox')->count());
		$this->assertContainsLang('AD_NAME_REQUIRED', $crawler->text());

		// Confirm error when submitting too long ad name
		$form_data = array(
			'ad_name'		=> 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec.',
		);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, $form_data);
		$this->assertGreaterThan(0, $crawler->filter('.errorbox')->count());
		$this->assertContains($this->lang('AD_NAME_TOO_LONG', 255), $crawler->text());

		// Create ad
		$form_data = array(
			'ad_name'		=> 'Functional test name',
			'ad_note'		=> 'Functional test note',
			'ad_code'		=> '<img src="https://www.phpbb.com/assets/images/images/logo_phpbb.png" />',
			'ad_enabled'	=> true,
		);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, $form_data);
		$this->assertGreaterThan(0, $crawler->filter('.successbox')->count());
		$this->assertContainsLang('ACP_AD_ADD_SUCCESS', $crawler->text());

		// Confirm new ad appears in the list and is enabled
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-admanagement-acp-main_module&mode=manage&sid={$this->sid}");
		$this->assertContains('Functional test name', $crawler->text());
		$this->assertContainsLang('ENABLED', $crawler->text());
	}

	/**
	* Test Advertisement management ACP edit page
	*/
	public function test_acp_edit()
	{
		// Load Advertisement management ACP page
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-admanagement-acp-main_module&mode=manage&sid={$this->sid}");

		// Hit edit button
		$edit_link = $crawler->filter('[title="' . $this->lang('EDIT') . '"]')->parents()->first()->link();
		$crawler = $this->click($edit_link);
		$this->assertContainsLang('ACP_ADS_EDIT', $crawler->filter('#main h1')->text());

		// Confirm error when submitting without required field data
		$form_data = array(
			'ad_name'		=> '',
			'ad_note'		=> '',
			'ad_code'		=> '',
			'ad_enabled'	=> false,
		);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, $form_data);
		$this->assertGreaterThan(0, $crawler->filter('.errorbox')->count());
		$this->assertContainsLang('AD_NAME_REQUIRED', $crawler->text());

		// Confirm error when submitting too long ad name
		$form_data = array(
			'ad_name'		=> 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec.',
		);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, $form_data);
		$this->assertGreaterThan(0, $crawler->filter('.errorbox')->count());
		$this->assertContains($this->lang('AD_NAME_TOO_LONG', 255), $crawler->text());

		// Create ad
		$form_data = array(
			'ad_name'		=> 'Functional test name edited',
			'ad_note'		=> 'Functional test note',
			'ad_code'		=> '<img src="https://www.phpbb.com/assets/images/images/logo_phpbb.png" />',
			'ad_enabled'	=> false,
		);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, $form_data);
		$this->assertGreaterThan(0, $crawler->filter('.successbox')->count());
		$this->assertContainsLang('ACP_AD_EDIT_SUCCESS', $crawler->text());

		// Confirm new ad appears in the list and is disabled
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-admanagement-acp-main_module&mode=manage&sid={$this->sid}");
		$this->assertContains('Functional test name edited', $crawler->text());
		$this->assertContainsLang('DISABLED', $crawler->text());
	}

	/**
	* Test Advertisement management ACP enable/disable
	*/
	public function test_acp_enable()
	{
		// Load Advertisement management ACP page
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-admanagement-acp-main_module&mode=manage&sid={$this->sid}");

		// Hit Disabled button
		$enable_link = $crawler->selectLink($this->lang('DISABLED'))->link();
		$crawler = $this->click($enable_link);
		$this->assertContainsLang('ACP_AD_ENABLE_SUCCESS', $crawler->text());

		// Load Advertisement management ACP page again
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-admanagement-acp-main_module&mode=manage&sid={$this->sid}");

		// Hit Enabled button
		$disable_link = $crawler->selectLink($this->lang('ENABLED'))->link();
		$crawler = $this->click($disable_link);
		$this->assertContainsLang('ACP_AD_DISABLE_SUCCESS', $crawler->text());
	}

	/**
	* Test Advertisement management ACP delete
	*/
	public function test_acp_delete()
	{
		// Load Advertisement management ACP page
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-admanagement-acp-main_module&mode=manage&sid={$this->sid}");

		// Hit delete button
		$delete_link = $crawler->filter('[title="' . $this->lang('DELETE') . '"]')->parents()->first()->link();
		$crawler = $this->click($delete_link);
		$this->assertContainsLang('CONFIRM_OPERATION', $crawler->text());

		// Confirm operation
		$form_data = array(
			'confirm'	=> $this->lang('YES'),
		);
		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form, $form_data);
		$this->assertContainsLang('ACP_AD_DELETE_SUCCESS', $crawler->text());

		// Confirm ad list is empty
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-admanagement-acp-main_module&mode=manage&sid={$this->sid}");
		$this->assertContainsLang('ACP_ADS_EMPTY', $crawler->filter('#main')->text());
	}

	static public function click($link)
	{
		return self::$client->click($link);
	}
}
