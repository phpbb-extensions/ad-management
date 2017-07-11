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
					'below_header',
					'after_first_post',
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
					'below_header',
					'after_posts',
					'after_profile',
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

		$sql = 'SELECT location_id
			FROM phpbb_ad_locations
			WHERE ad_id = ' . $ad_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->assertTrue(in_array($row['location_id'], $expected));
		}
		$this->db->sql_freeresult($result);
	}
}
