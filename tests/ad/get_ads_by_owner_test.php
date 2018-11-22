<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\ad;

class get_ads_by_owner_test extends ad_base
{
	/**
	 * Test data provider for test_get_ads_by_owner()
	 *
	 * @return array Array of test data
	 */
	public function get_ads_by_owner_data()
	{
		return array(
			array(1, array()),
			array(2, array(
				array(
					'ad_id'				=> '1',
					'ad_name'			=> 'Primary ad',
					'ad_enabled'		=> '1',
					'ad_start_date'		=> '1514764800',
					'ad_end_date'		=> '2051308800',
					'ad_views'			=> '0',
					'ad_views_limit'	=> '0',
					'ad_clicks'			=> '0',
					'ad_clicks_limit'	=> '0',
				),
				array(
					'ad_id'				=> '2',
					'ad_name'			=> 'Disabled ad',
					'ad_enabled'		=> '0',
					'ad_start_date'		=> '0',
					'ad_end_date'		=> '0',
					'ad_views'			=> '0',
					'ad_views_limit'	=> '0',
					'ad_clicks'			=> '0',
					'ad_clicks_limit'	=> '0',
				),
			)),
		);
	}

	/**
	 * Test get_ads_by_owner() method
	 *
	 * @dataProvider get_ads_by_owner_data
	 */
	public function test_get_ads($user_id, $expected)
	{
		$manager = $this->get_manager();

		$ads = $manager->get_ads_by_owner($user_id);

		$this->assertEquals($expected, $ads);
	}
}
