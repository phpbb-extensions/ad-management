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

class upload_test extends banner_base
{
	/**
	 * Test data provider for test_upload()
	 *
	 * @return array Array of test data
	 */
	public function upload_data()
	{
		return array(
			array(false),
			array(true),
		);
	}

	/**
	 * Test upload() method
	 *
	 * @dataProvider upload_data
	 */
	public function test_upload($file_move_success)
	{
		$manager = $this->get_manager();

		$this->files_upload->expects($this->once())
			->method('reset_vars');

		$this->files_upload->expects($this->once())
			->method('set_allowed_extensions')
			->with(array('gif', 'jpg', 'jpeg', 'png'));

		// Mock filespec
		$file = $this->getMockBuilder('\phpbb\files\filespec')
			->disableOriginalConstructor()
			->getMock();
		if (!$file_move_success)
		{
			$file->error[] = 'FILE_MOVE_UNSUCCESSFUL';
		}

		$this->files_upload->expects($this->once())
			->method('handle_upload')
			->with('files.types.form', 'banner')
			->willReturn($file);

		$file->expects($this->once())
			->method('clean_filename')
			->with('unique_ext');

		$file->expects($this->once())
			->method('move_file')
			->with('images/phpbb_ads')
			->willReturn($file_move_success);

		if (!$file_move_success)
		{
			$file->expects($this->once())
				->method('set_error')
				->with('FILE_MOVE_UNSUCCESSFUL');

			$this->expectException('\phpbb\exception\runtime_exception');
			$this->expectExceptionMessage('FILE_MOVE_UNSUCCESSFUL');

			$manager->upload();
		}
		else
		{
			$file->expects($this->once())
				->method('get')
				->with('realname')
				->willReturn('abcdef.jpg');

			$result = $manager->upload();

			$this->assertEquals('abcdef.jpg', $result);
		}
	}
}
