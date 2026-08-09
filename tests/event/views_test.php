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
		$this->user->page['page_name'] = 'viewtopic';
		$this->user->page['page_dir'] = '';
		$this->manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
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
				'ad_consent' => 1,
				'ad_views_enabled' => 1,
				'ad_clicks_enabled' => 0,
			)));

		$this->controller_helper->expects(($is_bot ? self::never() : self::once()))
			->method('route')
			->with('phpbb_ads_view', array(
				'data' => '1',
				'hash' => generate_link_hash('phpbb_ads_views_1'),
			))
			->willReturn('app.php/adsview/1');

		if (!$is_bot)
		{
			$this->template
				->expects(self::exactly(2))
				->method('assign_vars')
				->withConsecutive(
					[['AD_' => null, 'AD__ID' => 1, 'AD__CENTER' => false, 'AD__CLICK_URL' => '']],
					[['S_PHPBB_ADS_INCREMENT_VIEWS'	=> true, 'U_PHPBB_ADS_VIEWS'	=> 'app.php/adsview/1']]
				);
		}

		$listener = $this->get_listener();
		$listener->setup_ads();
	}

	/**
	 * Same ad in multiple placements counts once in page batch.
	 */
	public function test_duplicate_ad_ids_are_deduplicated()
	{
		$this->user->data['user_id'] = 10;
		$this->user->data['is_bot'] = false;
		$this->user->page['page_name'] = 'viewtopic';
		$this->user->page['page_dir'] = '';

		$this->manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->manager->method('load_memberships')->willReturn(array());
		$this->manager->method('get_ads')->willReturn(array(
			array(
				'ad_id' => 5,
				'ad_code' => '',
				'location_id' => 'above_header',
				'ad_centering' => false,
				'ad_consent' => 1,
				'ad_views_enabled' => 1,
				'ad_clicks_enabled' => 0,
			),
			array(
				'ad_id' => 5,
				'ad_code' => '',
				'location_id' => 'below_header',
				'ad_centering' => false,
				'ad_consent' => 1,
				'ad_views_enabled' => 1,
				'ad_clicks_enabled' => 0,
			),
		));

		$this->controller_helper->expects(self::once())
			->method('route')
			->with('phpbb_ads_view', array(
				'data' => '5',
				'hash' => generate_link_hash('phpbb_ads_views_5'),
			), true, '')
			->willReturn('app.php/adsview/5');

		$this->get_listener()->setup_ads();
	}
}
