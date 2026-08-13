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

class setup_ads_test extends main_listener_base
{
	/**
	* Test the setup_ads event
	*/
	public function test_setup_ads()
	{
		$this->user->data['user_id'] = 1;
		$this->user->page['page_name'] = 'viewtopic';
		$this->user->page['page_dir'] = '';
		$this->user->data['user_timezone'] = '';
		$user_groups = $this->manager->load_memberships($this->user->data['user_id']);
		$location_ids = $this->location_manager->get_all_location_ids();
		$ads = $this->manager->get_ads($location_ids, $user_groups, false);

		$this->template
			->expects(self::exactly(count($ads)))
			->method('assign_vars');

		$dispatcher = new \phpbb\event\dispatcher();
		$dispatcher->addListener('core.page_header_after', array($this->get_listener(), 'setup_ads'));
		$dispatcher->trigger_event('core.page_header_after');
	}
}
