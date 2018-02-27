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
		$this->request
			->expects($this->once())
			->method('is_set')
			->with($this->config['cookie_name'] . '_phpbb_ads_visual_demo', \phpbb\request\request_interface::COOKIE)
			->willReturn($in_visual_demo);

		$this->template
			->expects($this->exactly($in_visual_demo ? 9 : 0))
			->method('assign_vars');

		$this->template
			->expects($in_visual_demo ? $this->at(8) : $this->never())
		  	->method('assign_vars')
			->with(array(
				'S_PHPBB_ADS_VISUAL_DEMO'	=> true,
				'U_DISABLE_VISUAL_DEMO'		=> '?disable_visual_demo=true',
			));

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.page_footer_after', array($this->get_listener(), 'visual_demo'));
		$dispatcher->dispatch('core.page_footer_after');
	}
}
