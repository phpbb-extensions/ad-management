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
class location_base extends \phpbb_functional_test_case
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

	protected function create_ad($location)
	{
		// Load Advertisement management ACP page
		$crawler = self::request('GET', "adm/index.php?i=-phpbb-admanagement-acp-main_module&mode=manage&sid={$this->sid}");

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
		);

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, $form_data);

		return $form_data['ad_code'];
	}
}