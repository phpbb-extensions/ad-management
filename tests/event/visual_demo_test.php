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

class visual_demo_test extends main_listener_base
{
	/**
	 * Data for test_visual_demo
	 *
	 * @return array Array of test data
	 */
	public function data_visual_demo()
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
		$location_indeces = count($this->locations) - 1;

		$this->user->page['page_name'] = 'viewtopic';

		$this->request
			->method('is_set')
			->withConsecutive(
				[$this->config['cookie_name'] . '_phpbb_ads_visual_demo', \phpbb\request\request_interface::COOKIE],
				[$this->config['cookie_name'] . '_pop_up', \phpbb\request\request_interface::COOKIE]
			)
			->willReturnOnConsecutiveCalls($in_visual_demo, false);

		$this->controller_helper
			->expects($in_visual_demo ? self::once() : self::never())
			->method('route')
			->willReturnCallback(function ($route, array $params = array()) {
				return $route . '#' . serialize($params);
			});

		$this->template
			->expects(self::exactly($in_visual_demo ? $location_indeces : 0))
			->method('assign_vars');

		$this->template
			->expects($in_visual_demo ? self::at($location_indeces - 1) : self::never())
			->method('assign_vars')
			->with(array(
				'S_PHPBB_ADS_VISUAL_DEMO'	=> true,
				'U_DISABLE_VISUAL_DEMO'		=> 'phpbb_ads_visual_demo#' . serialize(array('action' => 'disable')),
			));

		$dispatcher = new \phpbb\event\dispatcher();
		$dispatcher->addListener('core.page_footer_after', array($this->get_listener(), 'visual_demo'));
		$dispatcher->trigger_event('core.page_footer_after');
	}
}
