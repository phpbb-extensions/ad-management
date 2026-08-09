<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\event;

class clicks_test extends main_listener_base
{
	/**
	 * Test per-ad click tracking setup.
	 *
	 * @dataProvider data_clicks
	 */
	public function test_clicks($enabled)
	{
		$this->user->data['user_id'] = 10;
		$this->user->data['is_bot'] = false;
		$this->user->page['page_name'] = 'viewtopic';
		$this->user->page['page_dir'] = '';

		$this->manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->manager->method('load_memberships')->willReturn(array());
		$this->manager->method('get_ads')->willReturn(array(array(
			'ad_id' => 7,
			'ad_code' => '',
			'location_id' => 'above_header',
			'ad_centering' => false,
			'ad_consent' => 1,
			'ad_views_enabled' => 0,
			'ad_clicks_enabled' => $enabled,
		)));

		$this->controller_helper->expects($enabled ? self::once() : self::never())
			->method('route')
			->with('phpbb_ads_click', array(
				'data' => 7,
				'hash' => generate_link_hash('phpbb_ads_click_7'),
			), true, '')
			->willReturn('app.php/adsclick/7');

		$this->template->expects($enabled ? self::once() : self::never())
			->method('assign_var')
			->with('S_PHPBB_ADS_ENABLE_CLICKS', true);

		$this->get_listener()->setup_ads();
	}

	/**
	 * @return array Test data
	 */
	public function data_clicks()
	{
		return array(
			array(false),
			array(true),
		);
	}
}
