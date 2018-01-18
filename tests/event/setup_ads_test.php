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
	* Data for test_setup_ads
	*
	* @return array Array of test data
	*/
	public function data_setup_ads()
	{
		return array(
			array(array(1), false),
			array(array(2), false),
			array(array(1, 2), false),
			array(array(2, 3), false),
			array(array(1), true),
		);
	}

	/**
	* Test the setup_ads event
	*
	* @dataProvider data_setup_ads
	*/
	public function test_setup_ads($hide_groups, $in_visual_demo)
	{
		if ($in_visual_demo)
		{
			$this->request
				->expects($this->once())
				->method('is_set')
				->with($this->config['cookie_name'] . '_phpbb_ads_visual_demo', \phpbb\request\request_interface::COOKIE)
				->willReturn(true);

			$this->template
				->expects($this->exactly(8))
				->method('assign_vars');
		}
		else
		{
			$this->user->data['user_id'] = 1;
			$user_groups = $this->manager->load_memberships($this->user->data['user_id']);

			$this->config_text->expects($this->once())
				->method('get')
				->with('phpbb_ads_hide_groups')
				->willReturn(json_encode($hide_groups));

			$ads = array();
			if (count(array_intersect($hide_groups, $user_groups)) === 0)
			{
				$location_ids = $this->location_manager->get_all_location_ids();
				$ads = $this->manager->get_ads($location_ids, false);
			}

			$this->template
				->expects($this->exactly(count($ads)))
				->method('assign_vars');
		}

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.page_header_after', array($this->get_listener(), 'setup_ads'));
		$dispatcher->dispatch('core.page_header_after');
	}
}
