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

use phpbb\ads\ad\manager;

class views_test extends main_listener_base
{
	/**
	 * Data for test_views_with_bots
	 *
	 * @return array Array of test data
	 */
	public function views_with_bots_data(): array
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
		$this->user->page['page_name'] = 'viewtopic';
		$this->user->page['page_dir'] = '';
		$this->config['phpbb_ads_enable_views'] = true;

		$this->manager = $this->getMockBuilder(manager::class)
			->disableOriginalConstructor()
			->getMock();

		$this->manager->expects(self::once())
			->method('load_memberships')
			->willReturn(array());

		$this->manager->expects(self::once())
			->method('get_ads')
			->willReturn(array(array(
				'ad_id'			=> '1',
				'ad_code'		=> '',
				'location_id'	=> '',
				'ad_centering'	=> '',
			)));

		$this->controller_helper->expects(($is_bot ? self::never() : self::once()))
			->method('route')
			->with('phpbb_ads_view', array('data' => '1'))
			->willReturn('app.php/adsview/1');

		if (!$is_bot)
		{
			$expectations = [
				['AD_' => '', 'AD__ID' => '1', 'AD__CENTER' => false],
				['S_INCREMENT_VIEWS' => true, 'U_PHPBB_ADS_VIEWS' => 'app.php/adsview/1']
			];
			$this->template
				->expects(self::exactly(2))
				->method('assign_vars')
				->willReturnCallback(function($arg) use (&$expectations) {
					$expectation = array_shift($expectations);
					self::assertEquals($expectation, $arg);
				});
		}

		$listener = $this->get_listener();
		$listener->setup_ads();
	}
}
