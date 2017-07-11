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

class delete_ad_locations_test extends ad_base
{
	/**
	 * Test delete_ad_locations() method
	 */
	public function test_delete_ad_locations()
	{
		$manager = $this->get_manager();

		$manager->delete_ad_locations(6);

		$sql = 'SELECT location_id
			FROM phpbb_ad_locations
			WHERE ad_id = 6';
		$result = $this->db->sql_query($sql);
		$affected_rows = $this->db->sql_affectedrows();
		$this->db->sql_freeresult($result);

		$this->assertEquals(0, $affected_rows);
	}
}
