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

class update_ad_test extends ad_base
{
	/**
	 * Test data provider for test_update_ad()
	 *
	 * @return array Array of test data
	 */
	public function update_ad_data()
	{
		return array(
			array(
				1,
				array(
					'ad_name'	=> 'Primary ad Updated',
				),
				1,
			),
			array(
				1,
				array(
					'ad_name'	=> 'Primary ad Updated #2',
					'ad_note'	=> 'Note Updated',
				),
				1,
			),
			array(
				0,
				array(
					'ad_name'	=> '',
				),
				0,
			),
			array(
				9999,
				array(
					'ad_name'	=> '',
				),
				0,
			),
		);
	}

	/**
	 * Test update_ad() method
	 *
	 * @dataProvider update_ad_data
	 */
	public function test_update_ad($ad_id, $data, $affected_rows)
	{
		$manager = $this->get_manager();

		$updated = $manager->update_ad($ad_id, $data);

		$this->assertEquals($affected_rows, $updated);

		$sql = 'SELECT ' . implode(', ', array_keys($data)) . '
			FROM phpbb_ads
			WHERE ad_id = ' . (int) $ad_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		foreach (array_keys($data) as $key)
		{
			$this->assertEquals($data[$key], $row[$key]);
		}
	}
}
