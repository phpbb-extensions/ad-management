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

class preview_ad_test extends main_listener_base
{
	/**
	* {@intheritDoc}
	*/
	public function setUp()
	{
		parent::setUp();
	}

	/**
	* Test the preview_ad event
	*/
	public function test_preview_ad()
	{
		$listener = $this->get_listener();

		$this->request->expects($this->once())
			->method('variable')
			->with('ad_preview', 0)
			->willReturn(1);

		$this->template->expects($this->once())
			->method('assign_vars')
			->with(array(
				'S_PREVIEWING_AD'	=> true,
				'L_PREVIEWING_AD'	=> 'PREVIEWING_AD',
			));

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.page_header_after', array($listener, 'preview_ad'));
		$dispatcher->dispatch('core.page_header_after');
	}
}
