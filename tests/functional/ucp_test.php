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
class ucp_test extends functional_base
{
	/**
	 * Test that Advertisement management UCP module appears only when user owns an ad
	 */
	public function test_ucp_module()
	{
		// Load Advertisement management UCP module and see it is really not accessible
		$crawler = $this->get_ucp_module(false);
		$this->assertContainsLang('MODULE_NOT_ACCESS', $crawler->text());

		$crawler = self::request('GET', "adm/index.php?i=-phpbb-ads-acp-main_module&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton($this->lang('ACP_ADS_ADD'))->form();
		$crawler = self::submit($form);
		$form_data = array(
			'ad_name'		=> 'Functional test UCP module',
			'ad_note'		=> 'Functional test UCP module note',
			'ad_code'		=> '<!-- SAMPLE ADD CODE -->',
			'ad_enabled'	=> true,
			'ad_end_date'	=> '2035-01-01',
			'ad_priority'	=> 1,
			'ad_views_limit'	=> 0,
			'ad_clicks_limit'	=> 0,
			'ad_owner'	=> 'admin',
		);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		self::submit($form, $form_data);

		// Load Advertisement management UCP module again. This time, it should be visible.
		$crawler = $this->get_ucp_module();

		// Assert Advertisement management module appears in sidebar
		$this->assertContainsLang('UCP_PHPBB_ADS_TITLE', $crawler->filter('.tabs')->text());
		$this->assertContainsLang('UCP_PHPBB_ADS_STATS', $crawler->filter('#active-subsection')->text());

		// Assert Advertisement management display appears
		$this->assertContainsLang('UCP_PHPBB_ADS_STATS', $crawler->filter('.panel-container h2')->text());
		$this->assertContainsLang('AD_NAME', $crawler->filter('.table1')->text());
	}

	protected function get_ucp_module($assert_response_html = true)
	{
		return self::request('GET', "ucp.php?i=-phpbb-ads-ucp-main_module&mode=stats&sid={$this->sid}", array(), $assert_response_html);
	}
}
