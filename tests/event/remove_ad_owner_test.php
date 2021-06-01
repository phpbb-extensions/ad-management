<?php
/**
 *
 * Pages extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\event;

class remove_ad_owner_test extends main_listener_base
{
	public function test_ad_owner()
	{
		$dispatcher = new \phpbb\event\dispatcher();
		$dispatcher->addListener('core.delete_user_after', array($this->get_listener(), 'remove_ad_owner'));
		$user_ids = array('999');
		$event_data = array('user_ids');
		$dispatcher->trigger_event('core.delete_user_after', compact($event_data));

		self::assertEmpty($this->manager->get_ads_by_owner(999));
	}
}
