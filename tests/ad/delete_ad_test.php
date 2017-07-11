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

class delete_ad_test extends ad_base
{
	/**
	 * Test delete_ad() method
	 */
	public function test_delete_ad()
	{
		$manager = $this->get_manager();

		$affected_rows = $manager->delete_ad(6);

		$this->assertEquals(1, $affected_rows);

		$sql = 'SELECT ad_id
			FROM phpbb_ads
			WHERE ad_id = 6';
		$result = $this->db->sql_query($sql);
		$affected_rows = $this->db->sql_affectedrows();
		$this->db->sql_freeresult($result);

		$this->assertEquals(0, $affected_rows);
	}
}
