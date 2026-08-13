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
		$hash = generate_link_hash('phpbb_ads_visual_demo_disable');
		$assigned_vars = array();

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
			->method('assign_vars')
			->willReturnCallback(function ($vars) use (&$assigned_vars)
			{
				$assigned_vars = array_merge($assigned_vars, $vars);
			});

		$dispatcher = new \phpbb\event\dispatcher();
		$dispatcher->addListener('core.page_footer_after', array($this->get_listener(), 'visual_demo'));
		$dispatcher->trigger_event('core.page_footer_after');

		if ($in_visual_demo)
		{
			self::assertSame(array(
				'CODE' => array(
					'visual_demo' => true,
					'name' => 'AD_ABOVE_HEADER',
					'desc' => 'AD_ABOVE_HEADER_DESC',
				),
				'ID' => 'above_header',
				'CENTER' => false,
				'CLICK_URL' => '',
			), $assigned_vars['AD_ABOVE_HEADER']);
			self::assertSame(true, $assigned_vars['S_PHPBB_ADS_VISUAL_DEMO']);
			self::assertSame('phpbb_ads_visual_demo#' . serialize(array(
				'action' => 'disable',
				'hash' => $hash,
			)), $assigned_vars['U_DISABLE_VISUAL_DEMO']);
		}
	}
}
