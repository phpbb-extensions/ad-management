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
class functional_base extends \phpbb_functional_test_case
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

		$this->add_lang_ext('phpbb/ads', array(
			'acp',
			'common',
			'info_acp_phpbb_ads',
			'info_ucp_phpbb_ads',
			'ucp',
		));

		$this->login();
		$this->admin_login();
	}

	protected function create_ad($location, $end_date = '', $content_only = false, $centering = true, $start_date = '')
	{
		// Load Advertisement management ACP page
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-ads-acp-main_module&mode=manage&sid={$this->sid}");

		// Jump to the add page
		$form = $crawler->selectButton($this->lang('ACP_ADS_ADD'))->form();
		$crawler = self::submit($form);

		// Create ad
		$form_data = array(
			'ad_name'		=> 'Functional test template location ' . $location,
			'ad_note'		=> '',
			'ad_code'		=> '<!-- SAMPLE ADD CODE ' . $location . ' -->',
			'ad_enabled'	=> true,
			'ad_locations'	=> array($location),
			'ad_start_date'	=> $start_date,
			'ad_end_date'	=> $end_date,
			'ad_priority'	=> 5,
			'ad_content_only'	=> $content_only,
			'ad_groups'		=> [],
			'ad_centering'	=> $centering,
		);

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, $form_data);
		$this->assertGreaterThan(0, $crawler->filter('.successbox')->count());
		$this->assertContainsLang('ACP_AD_ADD_SUCCESS', $crawler->text());

		return $form_data['ad_code'];
	}

	protected function disable_all_ads()
	{
		$sql = 'UPDATE phpbb_ads
			SET ad_enabled = 0';
		$this->db->sql_query($sql);
	}
}
