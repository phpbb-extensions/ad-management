<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\banner;

use phpbb\files\filespec;

class remove_test extends banner_base
{
	/**
	 * Test remove() method
	 */
	public function test_remove()
	{
		$manager = $this->get_manager();

		// Mock filespec
		$file = $this->getMockBuilder(filespec::class)
			->disableOriginalConstructor()
			->getMock();
		$manager->set_file($file);

		$file->expects(self::once())
			->method('remove');

		$manager->remove();
	}
}
