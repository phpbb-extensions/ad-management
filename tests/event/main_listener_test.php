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

use phpbb\ads\event\main_listener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener_test extends main_listener_base
{
	/**
	* Test the event listener is constructed correctly
	*/
	public function test_construct()
	{
		self::assertInstanceOf(EventSubscriberInterface::class, $this->get_listener());
	}

	/**
	* Test the event listener is subscribing events
	*/
	public function test_getSubscribedEvents()
	{
		self::assertEquals(array(
			'core.permissions',
			'core.user_setup',
			'core.page_footer_after',
			'core.page_header_after',
			'core.delete_user_after',
			'core.adm_page_header_after',
			'core.group_add_user_after',
			'core.group_delete_user_after',
		), array_keys(main_listener::getSubscribedEvents()));
	}
}
