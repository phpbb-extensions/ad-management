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

class banner_base extends \phpbb_test_case
{
	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\files\upload */
	protected $files_upload;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\filesystem\filesystem */
	protected $filesystem;

	/** @var string */
	protected $root_path;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\files\filespec */
	protected $file;

	protected static function setup_extensions()
	{
		return array('phpbb/ads');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path;

		$this->files_upload = $this->getMockBuilder('\phpbb\files\upload')
			->disableOriginalConstructor()
			->getMock();
		$this->filesystem = $this->getMockBuilder('\phpbb\filesystem\filesystem')
			->disableOriginalConstructor()
			->getMock();

		$this->root_path = $phpbb_root_path;
	}

	/**
	 * Returns fresh new banner manager.
	 *
	 * @return    \phpbb\ads\banner\banner    Banner manager
	 */
	public function get_manager()
	{
		return new \phpbb\ads\banner\banner(
			$this->files_upload,
			$this->filesystem,
			$this->root_path
		);
	}
}
