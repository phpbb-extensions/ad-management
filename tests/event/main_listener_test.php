<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\event;

class main_listener_test extends main_listener_base
{
	/**
	* Test the event listener is constructed correctly
	*/
	public function test_construct()
	{
		$listener = $this->get_listener();
		$this->assertInstanceOf('\Symfony\Component\EventDispatcher\EventSubscriberInterface', $listener);
	}

	/**
	* Test the event listener is subscribing events
	*/
	public function test_getSubscribedEvents()
	{
		$this->assertEquals(array(
			'core.permissions',
			'core.user_setup',
			'core.page_footer_after',
			'core.page_header_after',
			'core.delete_user_after',
			'core.adm_page_header_after',
		), array_keys(\phpbb\ads\event\main_listener::getSubscribedEvents()));
	}
}
