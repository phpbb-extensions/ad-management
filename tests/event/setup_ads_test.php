<?php
/**
 *
 * Pages extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\admanagement\tests\event;

class setup_ads_test extends main_listener_base
{
	/**
	* {@intheritDoc}
	*/
	public function setUp()
	{
		parent::setUp();
	}

	/**
	* Test the setup_ads event
	*/
	public function test_setup_ads()
	{
		$location_ids = $this->location_manager->get_all_location_ids();
		$ads = $this->manager->get_ads($location_ids);

		$this->config_text->expects($this->once())
			->method('get')
			->with('phpbb_admanagement_hide_groups')
			->willReturn('[]');

		// Reconstruct template calls
		$calls = array();
		foreach ($ads as $row)
		{
			$calls[] = array(
				'AD_' . strtoupper($row['location_id']) => htmlspecialchars_decode($row['ad_code']),
			);
		}

		$this->template->expects($this->exactly(count($ads)))
			->method('assign_vars')
			->will($this->returnValueMap($calls));

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.page_header_after', array($this->get_listener(), 'setup_ads'));
		$dispatcher->dispatch('core.page_header_after');
	}
}