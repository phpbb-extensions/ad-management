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

class delete_ad_groups_test extends ad_base
{
	/**
	 * Test delete_ad_groups() method.
	 */
	public function test_delete_ad_groups()
	{
		$manager = $this->get_manager();
		$manager->update_ad(6, array(
			'ad_name' => 'Delete Me Ad',
			'ad_groups' => array(2),
		));

		$manager->delete_ad_groups(6);

		$groups = array_filter($manager->load_groups(6), static function ($group) {
			return !empty($group['group_selected']);
		});
		self::assertEmpty($groups);
	}

	/**
	 * Test delete_ad_groups() with a non-existent advertisement.
	 */
	public function test_delete_ad_groups_no_ad()
	{
		$manager = $this->get_manager();
		$manager->update_ad(6, array(
			'ad_name' => 'Delete Me Ad',
			'ad_groups' => array(2),
		));

		$manager->delete_ad_groups(0);

		$groups = array_filter($manager->load_groups(6), static function ($group) {
			return !empty($group['group_selected']);
		});
		self::assertCount(1, $groups);
	}
}
