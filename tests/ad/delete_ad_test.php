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

		$this->assertNotEmpty($manager->get_ad(6));

		$affected_rows = $manager->delete_ad(6);

		$this->assertEquals(1, $affected_rows);

		$this->assertEmpty($manager->get_ad(6));
	}
}
