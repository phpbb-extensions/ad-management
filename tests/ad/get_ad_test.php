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
				'ad_end_date' => '2051308800',
				'ad_priority' => '5',
			)),
			array(0, false),
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
