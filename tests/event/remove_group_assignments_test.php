<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\event;

class remove_group_assignments_test extends main_listener_base
{
	public function test_remove_group_assignments()
	{
		$this->manager->update_ad(6, array(
			'ad_name' => 'Delete Me Ad',
			'ad_groups' => array(1, 2),
		));

		$dispatcher = new \phpbb\event\dispatcher();
		$dispatcher->addListener('core.delete_group_after', array($this->get_listener(), 'remove_group_assignments'));
		$group_id = 2;
		$dispatcher->trigger_event('core.delete_group_after', compact('group_id'));

		$selected_groups = array_filter($this->manager->load_groups(6), static function ($group) {
			return !empty($group['group_selected']);
		});

		self::assertCount(1, $selected_groups);
		self::assertSame(1, (int) reset($selected_groups)['group_id']);
	}
}
