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
class ucp_test extends \phpbb_functional_test_case
{
	/**
	 * {@inheritDoc}
	 */
	static protected function setup_extensions()
	{
		return array('phpbb/ads');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setUp()
	{
		parent::setUp();

		$this->add_lang_ext('phpbb/ads', array(
			'info_ucp_phpbb_ads',
			'ucp',
		));

		$this->login();
	}
	/**
	 * Test that Advertisement management UCP module appears
	 */
	public function test_ucp_module()
	{
		// Load Advertisement management ACP page
		$crawler = $this->get_ucp_module();

		// Assert Advertisement management module appears in sidebar
		$this->assertContainsLang('UCP_PHPBB_ADS_TITLE', $crawler->filter('.tabs')->text());
		$this->assertContainsLang('UCP_PHPBB_ADS_STATS', $crawler->filter('#active-subsection')->text());

		// Assert Advertisement management display appears
		$this->assertContainsLang('UCP_PHPBB_ADS_STATS', $crawler->filter('.panel-container h2')->text());
		$this->assertContainsLang('AD_NAME', $crawler->filter('.table1')->text());
	}

	protected function get_ucp_module()
	{
		return self::request('GET', "ucp.php?i=-phpbb-ads-ucp-main_module&mode=stats&sid={$this->sid}");
	}
}
