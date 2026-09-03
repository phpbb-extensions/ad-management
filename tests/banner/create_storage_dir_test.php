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
			'create directory and index' => array(false, false),
			'create index in existing directory' => array(true, false),
			'directory and index exist' => array(true, true),
		);
	}

	/**
	 * Test create_storage_dir() method
	 *
	 * @dataProvider create_storage_dir_data
	 */
	public function test_create_storage_dir($dir_exists, $index_exists)
	{
		$manager = $this->get_manager();
		$storage_path = $this->root_path . 'images/phpbb_ads';
		$index_path = $storage_path . '/index.htm';

		$this->filesystem->expects(self::exactly(2))
			->method('exists')
			->withConsecutive(array($storage_path), array($index_path))
			->willReturnOnConsecutiveCalls($dir_exists, $index_exists);

		if (!$dir_exists)
		{
			$this->filesystem->expects(self::once())
				->method('mkdir')
				->with($storage_path);
		}

		if (!$index_exists)
		{
			$this->filesystem->expects(self::once())
				->method('touch')
				->with($index_path);
		}

		$manager->create_storage_dir();
	}

	public function test_create_storage_dir_failure()
	{
		$storage_path = $this->root_path . 'images/phpbb_ads';
		$this->filesystem->expects(self::once())
			->method('exists')
			->with($storage_path)
			->willReturn(false);
		$this->filesystem->expects(self::once())
			->method('mkdir')
			->with($storage_path)
			->willThrowException(new \phpbb\filesystem\exception\filesystem_exception('FILESYSTEM_CANNOT_CREATE_DIRECTORY'));

		$this->expectException('\phpbb\filesystem\exception\filesystem_exception');
		$this->expectExceptionMessage('FILESYSTEM_CANNOT_CREATE_DIRECTORY');

		$this->get_manager()->create_storage_dir();
	}

	public function test_create_index_failure()
	{
		$storage_path = $this->root_path . 'images/phpbb_ads';
		$index_path = $storage_path . '/index.htm';
		$this->filesystem->expects(self::exactly(2))
			->method('exists')
			->withConsecutive(array($storage_path), array($index_path))
			->willReturnOnConsecutiveCalls(true, false);
		$this->filesystem->expects(self::once())
			->method('touch')
			->with($index_path)
			->willThrowException(new \phpbb\filesystem\exception\filesystem_exception('FILESYSTEM_CANNOT_TOUCH_FILES'));

		$this->expectException('\phpbb\filesystem\exception\filesystem_exception');
		$this->expectExceptionMessage('FILESYSTEM_CANNOT_TOUCH_FILES');

		$this->get_manager()->create_storage_dir();
	}
}
