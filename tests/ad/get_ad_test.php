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

class get_ad_test extends ad_base
{
	/**
	 * Test data provider for test_get_ad()
	 *
	 * @return array Array of test data
	 */
	public function get_ad_data()
	{
		return array(
			array(1, array(
				'ad_id' => '1',
				'ad_name' => 'Primary ad',
				'ad_note' => 'Ad description #1',
				'ad_code' => 'Ad Code #1',
				'ad_enabled' => '1',
				'ad_start_date' => '1514764800',
				'ad_end_date' => '2051308800',
				'ad_priority' => '5',
				'ad_views'	=> '0',
				'ad_clicks'	=> '0',
				'ad_views_limit'	=> '0',
				'ad_clicks_limit'	=> '0',
				'ad_owner'	=> '2',
				'ad_content_only' => '0',
				'ad_centering' => '1',
			)),
			array(0, array()),
		);
	}

	/**
	 * Test get_ad() method
	 *
	 * @dataProvider get_ad_data
	 */
	public function test_get_ads($ad_id, $expected)
	{
		$manager = $this->get_manager();

		$ad = $manager->get_ad($ad_id);

		$this->assertEquals($expected, $ad);
	}
}
