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

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\files\filespec_storage */
	protected $file;

	/** @var \phpbb\storage\storage */

	protected $storage;

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

		$this->files_upload = $this->getMockBuilder('\phpbb\files\upload')
			->disableOriginalConstructor()
			->getMock();
		$this->storage = $this->getMockBuilder('\phpbb\storage\storage')
			->disableOriginalConstructor()
			->getMock();
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
			$this->storage
		);
	}
}
