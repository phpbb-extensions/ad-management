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

class load_groups_test extends ad_base
{
	/**
	 * Test load_groups() method
	 */
	public function test_load_groups()
	{
		$manager = $this->get_manager();

		$groups = $manager->load_groups(0);

		$this->assertEquals(array(
			array(
				'group_id'			=> '1',
				'group_name'		=> 'ADMINISTRATORS',
				'group_selected'	=> '0',
			),
			array(
				'group_id'			=> '2',
				'group_name'		=> 'Custom group name',
				'group_selected'	=> '0',
			),
		), $groups);
	}
}
