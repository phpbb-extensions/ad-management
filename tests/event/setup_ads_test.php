<?php
/**
 *
 * Pages extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\event;

class setup_ads_test extends main_listener_base
{
	/**
	* Data for test_setup_ads
	*
	* @return array Array of test data
	*/
	public function data_setup_ads()
	{
		return array(
			array(array(1), '0', '0'),
			array(array(2), '1', '0'),
			array(array(1, 2), '0', '0'),
			array(array(2, 3), '1', '0'),
			array(array(1, 2), '0', '1'),
			array(array(2, 3), '1', '1'),
		);
	}

	/**
	* Test the setup_ads event
	*
	* @dataProvider data_setup_ads
	*/
	public function test_setup_ads($hide_groups, $allow_adblocker, $enable_clicks)
	{
		$this->user->data['user_id'] = 1;
		$user_groups = $this->manager->load_memberships($this->user->data['user_id']);

		$this->config_text->expects($this->once())
			->method('get')
			->with('phpbb_ads_hide_groups')
			->willReturn(json_encode($hide_groups));

		$ads = array();
		if (count(array_intersect($hide_groups, $user_groups)) === 0)
		{
			$location_ids = $this->location_manager->get_all_location_ids();
			$ads = $this->manager->get_ads($location_ids);
		}

		$this->template
			->expects($this->exactly(count($ads) + $enable_clicks))
			->method('assign_vars');

		$this->config['phpbb_ads_adblocker_message'] = $allow_adblocker;
		$this->config['phpbb_ads_enable_clicks'] = $enable_clicks;
		$this->template
			->expects($this->once())
			->method('assign_var')
			->with('S_DISPLAY_ADBLOCKER', $allow_adblocker);

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.page_header_after', array($this->get_listener(), 'setup_ads'));
		$dispatcher->dispatch('core.page_header_after');
	}

	/**
	 * Data for test_views_with_bots
	 *
	 * @return array Array of test data
	 */
	public function views_with_bots_data()
	{
		return array(
			array(true),
			array(false),
		);
	}

	/**
	 * Test that ad views are not being counted for BOT users
	 *
	 * @dataProvider views_with_bots_data
	 */
	public function test_views_with_bots($is_bot)
	{
		$this->user->data['user_id'] = 10;
		$this->user->data['is_bot'] = $is_bot;
		$this->config['phpbb_ads_enable_views'] = true;

		$this->config_text->expects($this->any())
			->method('get')
			->with('phpbb_ads_hide_groups')
			->willReturn(json_encode(array()));

		$this->manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->getMock();

		$this->manager->expects($this->any())
			->method('load_memberships')
			->willReturn(array());

		$this->manager->expects($this->any())
			->method('get_ads')
			->willReturn(array(array(
				'ad_id'			=> '1',
				'ad_code'		=> '',
				'location_id'	=> '',
			)));

		$this->controller_helper->expects(($is_bot ? $this->never() : $this->once()))
			->method('route')
			->with('phpbb_ads_view', array('data' => '1'))
			->willReturn('app.php/adsview/1');

		if (!$is_bot)
		{
			$this->template->expects($this->at(1))
				->method('assign_vars')
				->with(array(
					'S_INCREMENT_VIEWS'		=> true,
					'U_PHPBB_ADS_VIEWS'	=> "app.php/adsview/1",
				));
		}

		$listener = $this->get_listener();
		$listener->setup_ads();
	}
}
