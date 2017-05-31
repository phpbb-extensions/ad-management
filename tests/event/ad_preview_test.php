<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\admanagement\tests\event;

class ad_preview_test extends main_listener_base
{
	/**
	* {@intheritDoc}
	*/
	public function setUp()
	{
		parent::setUp();
	}

	/**
	* Test the ad_preview event
	*/
	public function test_ad_preview()
	{
		$listener = $this->get_listener();

		$this->request->expects($this->once())
			->method('variable')
			->with('ad_preview', 0)
			->willReturn(1);

		$this->template->expects($this->once())
			->method('assign_vars')
			->with(array(
				'S_AD_PREVIEW'	=> true,
				'AD_CODE'		=> 'admanagementcode',
			));

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.page_header_after', array($listener, 'setup_ads'));
		$dispatcher->dispatch('core.page_header_after');
	}
}
