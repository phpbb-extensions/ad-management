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

class adblocker_test extends main_listener_base
{
	/**
	 * Data for test_adblocker
	 *
	 * @return array Array of test data
	 */
	public function data_adblocker()
	{
		return array(
			array(0, array(), false), // disabled, not hidden for any group; should not display
			array(1, array(), true), // allowed, not hidden for any group; should display
			array(0, array(1), false), // disabled, hidden for group 1; should not display
			array(1, array(1), false), // allowed, hidden for group 1; should not display
			array(0, array(5), false), // disabled, hidden for group 5; should not display
			array(1, array(5), true), // allowed, hidden for group 5; should display
		);
	}

	/**
	 * Test the adblocker event
	 *
	 * @dataProvider data_adblocker
	 */
	public function test_adblocker($allow_adblocker, $hide_groups, $expected)
	{
		$this->user->data['user_id'] = 1;
		$this->config_text->expects($this->any())
			->method('get')
			->with('phpbb_ads_hide_groups')
			->willReturn(json_encode($hide_groups));

		$this->config['phpbb_ads_adblocker_message'] = $allow_adblocker;
		$this->template
			->expects($this->once())
			->method('assign_var')
			->with('S_DISPLAY_ADBLOCKER', $expected);

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.page_header_after', array($this->get_listener(), 'adblocker'));
		$dispatcher->dispatch('core.page_header_after');
	}
}
