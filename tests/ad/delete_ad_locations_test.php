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

		$this->assertNotEmpty($manager->get_ad_locations(6));

		$manager->delete_ad_locations(6);

		$this->assertEmpty($manager->get_ad_locations(6));
	}
}
