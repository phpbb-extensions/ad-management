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

		self::assertNotEmpty($manager->get_ad_locations(6));

		$manager->delete_ad_locations(6);

		self::assertEmpty($manager->get_ad_locations(6));
	}

	/**
	 * Test delete_ad_locations() method with non-existent ad
	 */
	public function test_delete_ad_locations_no_ad()
	{
		$manager = $this->get_manager();

		$sql = 'SELECT COUNT(ad_id) as total_ad_locations
			FROM phpbb_ad_locations';
		$result = $this->db->sql_query($sql);
		$total_ad_locations = $this->db->sql_fetchfield('total_ad_locations');
		$this->db->sql_freeresult($result);

		$manager->delete_ad_locations(0);

		$sql = 'SELECT COUNT(ad_id) as total_ad_locations
			FROM phpbb_ad_locations';
		$result = $this->db->sql_query($sql);
		self::assertEquals($this->db->sql_fetchfield('total_ad_locations'), $total_ad_locations);
		$this->db->sql_freeresult($result);
	}
}
