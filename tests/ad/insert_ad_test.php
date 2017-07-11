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

class insert_ad_test extends ad_base
{
	/**
	 * Test data provider for test_insert_ad()
	 *
	 * @return array Array of test data
	 */
	public function insert_ad_data()
	{
		return array(
			array(
				array(
					'ad_name'		=> 'Insert Ad Test #1',
					'ad_note'		=> '',
					'ad_code'		=> '',
					'ad_enabled'	=> 1,
					'ad_end_date'	=> 0,
					'ad_priority'	=> 5,
				),
			),
			array(
				array(
					'ad_name'		=> 'Insert Ad Test #2',
					'ad_note'		=> '',
					'ad_code'		=> '',
					'ad_enabled'	=> 1,
					'ad_end_date'	=> 0,
					'ad_priority'	=> 5,
					'random_column'	=> 'Random Value',
				),
			),
		);
	}

	/**
	 * Test insert_ad() method
	 *
	 * @dataProvider insert_ad_data
	 */
	public function test_insert_ad($data)
	{
		$manager = $this->get_manager();

		$ad_id = $manager->insert_ad($data);

		$this->assertGreaterThan(0, $ad_id);

		$sql = 'SELECT ad_name
			FROM phpbb_ads
			WHERE ad_id = ' . $ad_id;
		$result = $this->db->sql_query($sql);
		$ad_name = $this->db->sql_fetchfield('ad_name');
		$this->db->sql_freeresult($result);

		$this->assertEquals($data['ad_name'], $ad_name);
	}
}
