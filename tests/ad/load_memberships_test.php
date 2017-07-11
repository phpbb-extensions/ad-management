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

class load_memberships_test extends ad_base
{
	/**
	 * Test load_memberships() method
	 */
	public function test_load_memberships()
	{
		$manager = $this->get_manager();

		$memberships = $manager->load_memberships(1);

		$this->assertEquals(array(
			1,
			3,
		), $memberships);
	}
}
