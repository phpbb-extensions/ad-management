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

class remove_test extends banner_base
{
	/**
	 * Test remove() method
	 */
	public function test_remove()
	{
		$manager = $this->get_manager();

		// Mock filespec
		$file = $this->getMockBuilder('\phpbb\files\filespec_storage')
			->disableOriginalConstructor()
			->getMock();
		$manager->set_file($file);

		$file->expects(self::once())
			->method('remove')
			->with($this->storage);

		$manager->remove();
	}
}
