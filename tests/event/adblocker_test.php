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
			array(0, false), // disabled; should not display
			array(1, true), // allowed; should display
		);
	}

	/**
	 * Test the adblocker event
	 *
	 * @dataProvider data_adblocker
	 */
	public function test_adblocker($allow_adblocker, $expected)
	{
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
