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

class manage_visual_demo_test extends main_listener_base
{
	/**
	 * Data set for test_manage_visual_demo
	 *
	 * @return array
	 */
	public function manage_visual_demo_data()
	{
		return array(
			array(true, true),
			array(false, true),
			array(false, false),
		);
	}

	/**
	 * Test manage_visual_demo
	 *
	 * @dataProvider manage_visual_demo_data
	 */
	public function test_manage_visual_demo($enable_visual_demo, $disable_visual_demo)
	{
		$this->request->expects($this->at(0))
			->method('is_set')
			->with('_phpbb_ads_visual_demo', \phpbb\request\request_interface::COOKIE)
			->willReturn($enable_visual_demo);

		$this->request->expects($this->at(1))
			->method('is_set')
			->with('enable_visual_demo')
			->willReturn($enable_visual_demo);

		if ($enable_visual_demo)
		{
			$this->user->expects($this->once())
				->method('set_cookie')
				->with('phpbb_ads_visual_demo', '', 0);

			// redirect() will throw this, but it's ok
			$this->setExpectedTriggerError(E_USER_ERROR, 'INSECURE_REDIRECT');
		}
		else
		{
			$this->request->expects($this->at(2))
				->method('is_set')
				->with('disable_visual_demo')
				->willReturn($disable_visual_demo);

			if ($disable_visual_demo)
			{
				$this->user->expects($this->once())
					->method('set_cookie')
					->with('phpbb_ads_visual_demo', '', 1);

				$this->template->expects($this->once())
					->method('assign_var')
					->with('S_PHPBB_ADS_VISUAL_DEMO', false);

				$this->setExpectedTriggerError(E_USER_NOTICE, 'VISUAL_DEMO_DISABLED');
			}
		}

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.index_modify_page_title', array($this->get_listener(), 'manage_visual_demo'));
		$dispatcher->dispatch('core.index_modify_page_title');
	}
}
