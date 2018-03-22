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

class remove_ad_owner_test extends ad_base
{
	/**
	 * Test data provider for test_remove_ad_owner()
	 *
	 * @return array Array of test data
	 */
	public function remove_ad_owner_data()
	{
		return array(
			array(array('2')),
			array(array('3', '4')),
			array(array()),
		);
	}

	/**
	 * Test remove_ad_owner() method
	 *
	 * @dataProvider remove_ad_owner_data
	 */
	public function test_remove_ad_owner($user_ids)
	{
		$manager = $this->get_manager();

		foreach ($user_ids as $user_id)
		{
			$this->assertNotEmpty($manager->get_ads_by_owner($user_id));
		}

		$manager->remove_ad_owner($user_ids);

		foreach ($user_ids as $user_id)
		{
			$this->assertEmpty($manager->get_ads_by_owner($user_id));
		}
	}
}
