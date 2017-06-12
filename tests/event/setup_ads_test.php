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
	* Data for test_setup_ads
	*
	* @return array Array of test data
	*/
	public function data_setup_ads()
	{
		return array(
			array(array(1)),
			array(array(2)),
		);
	}

	/**
	* Test the setup_ads event
	*
	* @dataProvider data_setup_ads
	*/
	public function test_setup_ads($hide_groups)
	{
		$this->user->data['user_id'] = 1;

		$this->config_text->expects($this->once())
			->method('get')
			->with('phpbb_admanagement_hide_groups')
			->willReturn(json_encode($hide_groups));

		if ($hide_groups == array(2))
		{
			$location_ids = $this->location_manager->get_all_location_ids();
			$ads = $this->manager->get_ads($location_ids);

			$this->template->expects($this->exactly(count($ads)))
				->method('assign_vars');
		}

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.page_header_after', array($this->get_listener(), 'setup_ads'));
		$dispatcher->dispatch('core.page_header_after');
	}
}