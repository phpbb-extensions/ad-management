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
	 * Test data provider for test_load_memberships()
	 *
	 * @return array Array of test data
	 */
	public static function load_memberships_data(): array
	{
		return array(
			array(
				1, array(1,3),
				2, array(),
				null, array(),
				0, array(),
			),
		);
	}

	/**
	 * Test load_memberships() method
	 *
	 * @dataProvider load_memberships_data
	 */
	public function test_load_memberships($user_id, $user_groups)
	{
		$manager = $this->get_manager();

		$memberships = $manager->load_memberships($user_id);

		self::assertEquals($user_groups, $memberships);
	}
}
