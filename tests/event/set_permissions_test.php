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

class set_permissions_test extends main_listener_base
{
	/**
	 * Test the set_permissions_test event
	 */
	public function test_set_permissions()
	{
		$dispatcher = new \phpbb\event\dispatcher();
		$dispatcher->addListener('core.permissions', array($this->get_listener(), 'set_permissions'));

		$permissions = array();
		$event_data = array('permissions');
		$event_data_after = $dispatcher->trigger_event('core.permissions', compact($event_data));
		extract($event_data_after, EXTR_OVERWRITE);

		self::assertEquals(array(
			'u_phpbb_ads'	=> array('lang' => 'ACL_U_PHPBB_ADS', 'cat' => 'misc'),
		), $permissions);
	}
}
