<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\event;

class destroy_user_group_cache_test extends main_listener_base
{
	/**
	 * Test destroy_user_group_cache
	 */
	public function test_destroy_user_group_cache()
	{
		$this->cache
			->expects($this->exactly(2))
			->method('destroy')
			->with('sql', USER_GROUP_TABLE);

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.group_add_user_after', array($this->get_listener(), 'destroy_user_group_cache'));
		$dispatcher->dispatch('core.group_add_user_after');

		$dispatcher->addListener('core.group_delete_user_after', array($this->get_listener(), 'destroy_user_group_cache'));
		$dispatcher->dispatch('core.group_delete_user_after');
	}
}
