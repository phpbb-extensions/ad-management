<?php
/**
 *
 * Pages extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\event;

use phpbb\event\dispatcher;
use phpbb\request\request_interface;

class visual_demo_test extends main_listener_base
{
	/**
	 * Data for test_visual_demo
	 *
	 * @return array Array of test data
	 */
	public function data_visual_demo(): array
	{
		return array(
			array(true),
			array(false),
		);
	}

	/**
	 * Test the visual_demo event
	 *
	 * @dataProvider data_visual_demo
	 */
	public function test_visual_demo($in_visual_demo)
	{
		$location_indexes = count($this->locations) - 1;

		$this->user->page['page_name'] = 'viewtopic';

		$expectations = [
			[$this->config['cookie_name'] . '_phpbb_ads_visual_demo', request_interface::COOKIE],
			[$this->config['cookie_name'] . '_pop_up', request_interface::COOKIE]
		];
		$return_values = [$in_visual_demo, false];
		$this->request
			->method('is_set')
			->willReturnCallback(function($arg1, $arg2) use (&$expectations, &$return_values) {
				$expectation = array_shift($expectations);
				self::assertEquals($expectation[0], $arg1);
				self::assertEquals($expectation[1], $arg2);
				return array_shift($return_values);
			});

		$this->controller_helper
			->expects($in_visual_demo ? self::once() : self::never())
			->method('route')
			->willReturnCallback(function ($route, array $params = array()) {
				return $route . '#' . serialize($params);
			});

		$this->template
			->expects(self::exactly($in_visual_demo ? $location_indexes : 0))
			->method('assign_vars');

		$this->template
			->expects(self::exactly($in_visual_demo ? $location_indexes : 0))
			->method('assign_vars')
			->willReturnCallback(function($params) use ($location_indexes) {
				static $callCount = 0;
				$callCount++;
				if ($callCount === $location_indexes) {
					$this->assertEquals([
						'S_PHPBB_ADS_VISUAL_DEMO'	=> true,
						'U_DISABLE_VISUAL_DEMO'		=> 'phpbb_ads_visual_demo#' . serialize(['action' => 'disable']),
					], $params);
					return true;
				}
				return true;
			});

		$dispatcher = new dispatcher();
		$dispatcher->addListener('core.page_footer_after', array($this->get_listener(), 'visual_demo'));
		$dispatcher->trigger_event('core.page_footer_after');
	}
}
