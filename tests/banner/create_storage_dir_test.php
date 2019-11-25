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

class create_storage_dir_test extends banner_base
{
	/**
	 * Test data provider for test_create_storage_dir()
	 *
	 * @return array Array of test data
	 */
	public function create_storage_dir_data()
	{
		return array(
			array(false, false),
			array(false, true),
			array(true, false),
			array(true, true),
		);
	}

	/**
	 * Test create_storage_dir() method
	 *
	 * @dataProvider create_storage_dir_data
	 */
	public function test_create_storage_dir($dir_exists, $success)
	{
		$manager = $this->get_manager();

		$this->filesystem->expects($this->once())
			->method('exists')
			->with($this->root_path . 'images/phpbb_ads')
			->willReturn($dir_exists);

		if (!$dir_exists)
		{
			$mkdir = $this->filesystem->expects($this->once())
				->method('mkdir')
				->with($this->root_path . 'images/phpbb_ads');

			if (!$success)
			{
				$mkdir->willThrowException(new \phpbb\filesystem\exception\filesystem_exception('CANNOT_CREATE_DIRECTORY'));

				$this->expectException('\phpbb\filesystem\exception\filesystem_exception');
				$this->expectExceptionMessage('CANNOT_CREATE_DIRECTORY');
			}
		}

		$manager->create_storage_dir();
	}
}
