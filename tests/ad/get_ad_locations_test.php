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

class get_ad_locations_test extends ad_base
{
	/**
	 * Test data provider for test_get_ad_locations()
	 *
	 * @return array Array of test data
	 */
	public function get_ad_locations_data(): array
	{
		return array(
			array(
				1,
				array(
					'above_header',
					'after_profile',
					'below_header',
				),
			),
			array(
				2,
				array(
					'above_header',
					'below_header',
				),
			),
			array(
				3,
				array(),
			),
			array(
				0,
				array(),
			),
		);
	}

	/**
	 * Test get_ad_locations() method
	 *
	 * @dataProvider get_ad_locations_data
	 */
	public function test_get_ad_locations($ad_id, $expected)
	{
		$manager = $this->get_manager();

		$ad_locations = $manager->get_ad_locations($ad_id);

		self::assertEquals($expected, $ad_locations);
	}
}
