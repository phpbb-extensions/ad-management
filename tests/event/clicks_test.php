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

class clicks_test extends main_listener_base
{
	/**
	 * Data for test_clicks
	 *
	 * @return array Array of test data
	 */
	public function data_clicks()
	{
		return array(
			array('0'),
			array('1'),
		);
	}

	/**
	 * Test the adblocker event
	 *
	 * @dataProvider data_clicks
	 */
	public function test_clicks($enable_clicks)
	{
		$this->config['phpbb_ads_enable_clicks'] = $enable_clicks;

		$this->controller_helper->expects($enable_clicks ? $this->once() : $this->never())
			->method('route')
			->with('phpbb_ads_click', array('data' => 0))
			->willReturn('app.php/adsclick/0');

		$this->template
			->expects($enable_clicks ? $this->once() : $this->never())
			->method('assign_vars')
			->with(array(
				'U_PHPBB_ADS_CLICK'		=> 'app.php/adsclick/0',
				'S_PHPBB_ADS_ENABLE_CLICKS'	=> true,
			));

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.page_header_after', array($this->get_listener(), 'clicks'));
		$dispatcher->dispatch('core.page_header_after');
	}
}
