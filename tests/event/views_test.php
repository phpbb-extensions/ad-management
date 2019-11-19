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

class views_test extends main_listener_base
{
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

		$this->manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->getMock();

		$this->manager->expects($this->once())
			->method('load_memberships')
			->willReturn(array());

		$this->manager->expects($this->once())
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
					'S_INCREMENT_VIEWS'	=> true,
					'U_PHPBB_ADS_VIEWS'	=> 'app.php/adsview/1',
				));
		}

		$listener = $this->get_listener();
		$listener->setup_ads();
	}
}
