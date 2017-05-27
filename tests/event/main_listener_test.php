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
			'core.page_header_after',
		), array_keys(\phpbb\admanagement\event\main_listener::getSubscribedEvents()));
	}
}