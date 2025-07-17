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

use Symfony\Component\DomCrawler\Crawler;

/**
* @group functional
*/
class acp_manage_test extends functional_base
{
	/**
	* Test that Advertisement management ACP module appears
	*/
	public function test_acp_manage_module()
	{
		// Load Advertisement management ACP page
		$crawler = $this->get_manage_page();

		// Assert Advertisement management module appears in sidebar
		$this->assertContainsLang('ACP_PHPBB_ADS_TITLE', $crawler->filter('.menu-block')->text());
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
		$crawler = $this->get_manage_page();

		// Jump to the add ad page
		$form = $crawler->selectButton($this->lang('ACP_ADS_ADD'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('ACP_ADS_ADD', $crawler->filter('#main h1')->text());

		// Confirm ad code analysis
		$form = $crawler->selectButton($this->lang('ANALYSE_AD_CODE'))->form();
		$crawler = self::submit($form, array(
			'ad_code'	=> '<script src="">alert();window.location.href=""</script>',
		));
		self::assertStringContainsString('Non-asynchronous javascript', $crawler->filter('.analyser-results')->html());
		self::assertStringContainsString('Usage of <samp>alert()</samp>', $crawler->filter('.analyser-results')->html());
		self::assertStringContainsString('Redirection', $crawler->filter('.analyser-results')->html());

		// Confirm error when submitting without required field data
		$this->submit_with_error($crawler, array(), $this->lang('AD_NAME_REQUIRED'));

		// Confirm error when submitting too long ad name
		$form_data = array(
			'ad_name'		=> str_repeat('a', 256),
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_NAME_TOO_LONG', 255));

		// Confirm error when submitting old start date
		$form_data = array(
			'ad_start_date'	=> '2000-01-01',
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_START_DATE_INVALID'));

		// Confirm error when submitting older end date than start date
		$form_data = array(
			'ad_start_date'	=> '2018-01-01',
			'ad_end_date'	=> '2017-01-01',
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('END_DATE_TOO_SOON'));

		// Confirm error when submitting old end date
		$form_data = array(
			'ad_end_date'	=> '2000-01-01',
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_END_DATE_INVALID'));

		// Confirm error when submitting too low priority
		$form_data = array(
			'ad_priority'	=> 0,
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_PRIORITY_INVALID'));

		// Confirm error when submitting too high priority
		$form_data = array(
			'ad_priority'	=> 11,
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_PRIORITY_INVALID'));

		// Confirm error when submitting too low views limit
		$form_data = array(
			'ad_views_limit'	=> -1,
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_VIEWS_LIMIT_INVALID'));

		// Confirm error when submitting too low clicks limit
		$form_data = array(
			'ad_clicks_limit'	=> -1,
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_CLICKS_LIMIT_INVALID'));

		// Confirm error when submitting the wrong username for an ad owner
		$form_data = array(
			'ad_owner'	=> 'non-existent user',
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_OWNER_INVALID'));

		// Create ad
		$form_data = array(
			'ad_name'		=> 'Functional test name',
			'ad_note'		=> 'Functional test note',
			'ad_code'		=> '<!-- SAMPLE ADD CODE -->',
			'ad_enabled'	=> 1,
			'ad_start_date'	=> '2030-01-01',
			'ad_end_date'	=> '2035-01-01',
			'ad_priority'	=> 1,
			'ad_views_limit'	=> 0,
			'ad_clicks_limit'	=> 0,
			'ad_owner'	=> 'admin',
			'ad_groups'	=> [],
			'ad_centering'	=> 1,
		);

		// Confirm preview
		$form = $crawler->selectButton($this->lang('PREVIEW'))->form();
		$crawler = self::submit($form, $form_data);
		self::assertGreaterThan(0, $crawler->filter('.phpbb-ads-center')->count());
		self::assertStringContainsString($form_data['ad_code'], $crawler->filter('.phpbb-ads-center')->html());

		// Confirm ad added
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, $form_data);
		self::assertGreaterThan(0, $crawler->filter('.successbox')->count());
		$this->assertContainsLang('ACP_AD_ADD_SUCCESS', $crawler->text());

		// Confirm new ad appears in the list, is enabled and end date is displayed correctly
		$crawler = $this->get_manage_page();
		self::assertStringContainsString('Functional test name', $crawler->text());
		$this->assertContainsLang('ENABLED', $crawler->text());
		self::assertStringContainsString('2035-01-01', $crawler->text());

		// Confirm the log entry has been added correctly
		$crawler = self::request('GET', "adm/index.php?i=acp_logs&mode=admin&sid=$this->sid");
		self::assertStringContainsString(strip_tags($this->lang('ACP_PHPBB_ADS_ADD_LOG', $form_data['ad_name'])), $crawler->text());
	}

	/**
	* Test Advertisement management ACP edit page
	*/
	public function test_acp_edit()
	{
		// Load Advertisement management ACP page
		$crawler = $this->get_manage_page();

		// Hit the edit button
		$edit_link = $crawler->filter('[title="' . $this->lang('EDIT') . '"]')->ancestors()->first()->link();
		$crawler = static::click($edit_link);
		$this->assertContainsLang('ACP_ADS_EDIT', $crawler->filter('#main h1')->text());

		// Confirm error when submitting without required field data
		$form_data = array(
			'ad_name'		=> '',
			'ad_note'		=> '',
			'ad_code'		=> '',
			'ad_enabled'	=> 0,
			'ad_start_date'	=> '',
			'ad_end_date'	=> '',
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_NAME_REQUIRED'));

		// Confirm error when submitting too long ad name
		$form_data = array(
			'ad_name'		=> str_repeat('a', 256),
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_NAME_TOO_LONG', 255));

		// Confirm error when submitting old start date
		$form_data = array(
			'ad_start_date'	=> '2000-01-01',
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_START_DATE_INVALID'));

		// Confirm error when submitting older end date than start date
		$form_data = array(
			'ad_start_date'	=> '2018-01-01',
			'ad_end_date'	=> '2017-01-01',
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('END_DATE_TOO_SOON'));

		// Confirm error when submitting old end date
		$form_data = array(
			'ad_end_date'	=> '2000-01-01',
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_END_DATE_INVALID'));

		// Confirm error when submitting too low priority
		$form_data = array(
			'ad_priority'	=> 0,
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_PRIORITY_INVALID'));

		// Confirm error when submitting too high priority
		$form_data = array(
			'ad_priority'	=> 11,
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_PRIORITY_INVALID'));

		// Confirm error when submitting too low views limit
		$form_data = array(
			'ad_views_limit'	=> -1,
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_VIEWS_LIMIT_INVALID'));

		// Confirm error when submitting too low clicks limit
		$form_data = array(
			'ad_clicks_limit'	=> -1,
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_CLICKS_LIMIT_INVALID'));

		// Confirm error when submitting the wrong username for an ad owner
		$form_data = array(
			'ad_owner'	=> 'non-existent user',
		);
		$this->submit_with_error($crawler, $form_data, $this->lang('AD_OWNER_INVALID'));

		// Edit ad
		$form_data = array(
			'ad_name'		=> 'Functional test name edited',
			'ad_note'		=> 'Functional test note',
			'ad_code'		=> '<!-- SAMPLE ADD CODE EDITED -->',
			'ad_enabled'	=> 0,
			'ad_start_date'	=> '2030-01-02',
			'ad_end_date'	=> '2035-01-02',
			'ad_priority'	=> 2,
			'ad_views_limit'	=> 0,
			'ad_clicks_limit'	=> 0,
			'ad_owner'	=> 'admin',
			'ad_groups'	=> [],
			'ad_centering'	=> 1,
		);

		// Confirm preview
		$form = $crawler->selectButton($this->lang('PREVIEW'))->form();
		$crawler = self::submit($form, $form_data);
		self::assertGreaterThan(0, $crawler->filter('.phpbb-ads-center')->count());
		self::assertStringContainsString($form_data['ad_code'], $crawler->filter('.phpbb-ads-center')->html());

		// Confirm edit
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, $form_data);
		self::assertGreaterThan(0, $crawler->filter('.successbox')->count());
		$this->assertContainsLang('ACP_AD_EDIT_SUCCESS', $crawler->text());

		// Confirm a new ad appears in the list, is disabled and start and end date is present and updated
		$crawler = $this->get_manage_page();
		self::assertStringContainsString('Functional test name edited', $crawler->text());
		$this->assertContainsLang('DISABLED', $crawler->text());
		self::assertStringContainsString('2030-01-02', $crawler->text());
		self::assertStringContainsString('2035-01-02', $crawler->text());

		// Confirm the log entry has been added correctly
		$crawler = self::request('GET', "adm/index.php?i=acp_logs&mode=admin&sid=$this->sid");
		self::assertStringContainsString(strip_tags($this->lang('ACP_PHPBB_ADS_EDIT_LOG', $form_data['ad_name'])), $crawler->text());
	}

	/**
	* Test Advertisement management ACP enable/disable
	*/
	public function test_acp_enable()
	{
		// Load Advertisement management ACP page
		$crawler = $this->get_manage_page();

		// Hit the Disabled button
		$enable_link = $crawler->selectLink($this->lang('DISABLED'))->link();
		$crawler = static::click($enable_link);
		$this->assertContainsLang('ACP_AD_ENABLE_SUCCESS', $crawler->text());

		// Load Advertisement management ACP page again
		$crawler = $this->get_manage_page();

		// Hit the Enabled button
		$disable_link = $crawler->selectLink($this->lang('ENABLED'))->link();
		$crawler = static::click($disable_link);
		$this->assertContainsLang('ACP_AD_DISABLE_SUCCESS', $crawler->text());
	}

	/**
	* Test Advertisement management ACP delete
	*/
	public function test_acp_delete()
	{
		// Load Advertisement management ACP page
		$crawler = $this->get_manage_page();

		// Hit delete button
		$delete_link = $crawler->filter('[title="' . $this->lang('DELETE') . '"]')->ancestors()->first()->link();
		$crawler = static::click($delete_link);
		$this->assertContainsLang('CONFIRM_OPERATION', $crawler->text());

		// Confirm operation
		$form_data = array(
			'confirm'	=> $this->lang('YES'),
		);
		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form, $form_data);
		$this->assertContainsLang('ACP_AD_DELETE_SUCCESS', $crawler->text());

		// Confirm an ad list is empty
		$crawler = $this->get_manage_page();
		$this->assertContainsLang('ACP_ADS_EMPTY', $crawler->filter('#main')->text());

		// Confirm the log entry has been added correctly
		$crawler = self::request('GET', "adm/index.php?i=acp_logs&mode=admin&sid=$this->sid");
		self::assertStringContainsString(strip_tags($this->lang('ACP_PHPBB_ADS_EDIT_LOG', 'Functional test name edited')), $crawler->text());
	}

	public static function click($link): Crawler
	{
		return self::$client->click($link);
	}

	protected function get_manage_page(): Crawler
	{
		return self::request('GET', "adm/index.php?i=-phpbb-ads-acp-main_module&mode=manage&sid=$this->sid");
	}

	protected function submit_with_error($crawler, $form_data, $error_lang): void
	{
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, $form_data);
		self::assertGreaterThan(0, $crawler->filter('.errorbox')->count());
		self::assertStringContainsString($error_lang, $crawler->text());
	}
}
