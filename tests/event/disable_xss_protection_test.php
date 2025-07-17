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

use phpbb\event\data;

class disable_xss_protection_test extends main_listener_base
{
	/**
	 * Data set for test_disable_xss_protection
	 *
	 * @return array
	 */
	public function disable_xss_protection_data(): array
	{
		return array(
			// only add new header to Chrome browsers on the phpbb-ads acp page
			array(array('foo-header' => 'foo-value'), 'chrome', 'phpbb-ads', true),
			array(array(), 'chrome', 'phpbb-ads', true),
			// do not add a new header to any other browsers or acp pages
			array(array(), 'msie', 'phpbb-ads', false),
			array(array(), 'chrome', 'acp-foo', false),
			array(array(), '', 'phpbb-ads', false),
			array(array(), 'chrome', '', false),
		);
	}

	/**
	 * Test disable_xss_protection
	 *
	 * @dataProvider disable_xss_protection_data
	 */
	public function test_disable_xss_protection($data, $browser, $page, $expected)
	{
		$this->user->browser = $browser;
		$this->user->page['page'] = $page;

		$event = new data(array('http_headers' => $data));
		$listener = $this->get_listener();

		$listener->disable_xss_protection($event);

		self::assertEquals($expected, array_key_exists('X-XSS-Protection', $event['http_headers']));
	}
}
