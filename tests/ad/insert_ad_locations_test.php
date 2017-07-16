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

class insert_ad_locations_test extends ad_base
{
	/**
	 * Test data provider for test_insert_ad_locations()
	 *
	 * @return array Array of test data
	 */
	public function insert_ad_locations_data()
	{
		return array(
			array(
				1,
				array(
					'after_first_post',
				),
				array(
					'above_header',
					'after_first_post',
					'after_profile',
					'below_header',
				),
			),
			array(
				2,
				array(
					'after_posts',
					'after_profile',
				),
				array(
					'above_header',
					'after_posts',
					'after_profile',
					'below_header',
				),
			),
		);
	}

	/**
	 * Test insert_ad_locations() method
	 *
	 * @dataProvider insert_ad_locations_data
	 */
	public function test_insert_ad_locations($ad_id, $ad_locations, $expected)
	{
		$manager = $this->get_manager();

		$manager->insert_ad_locations($ad_id, $ad_locations);

		$this->assertEquals($manager->get_ad_locations($ad_id), $expected);
	}
}
