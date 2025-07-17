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

use phpbb\ads\banner\banner;
use phpbb\files\filespec;
use phpbb\files\upload;
use phpbb\filesystem\filesystem;
use phpbb_test_case;
use PHPUnit\Framework\MockObject\MockObject;

class banner_base extends phpbb_test_case
{
	/** @var MockObject|upload */
	protected MockObject|upload $files_upload;

	/** @var MockObject|filesystem */
	protected filesystem|MockObject $filesystem;

	/** @var string */
	protected string $root_path;

	/** @var MockObject|filespec */
	protected MockObject|filespec $file;

	protected static function setup_extensions(): array
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

		$this->files_upload = $this->getMockBuilder(upload::class)
			->disableOriginalConstructor()
			->getMock();
		$this->filesystem = $this->getMockBuilder(filesystem::class)
			->disableOriginalConstructor()
			->getMock();

		$this->root_path = $phpbb_root_path;
	}

	/**
	 * Returns fresh new banner manager.
	 *
	 * @return    banner    Banner manager
	 */
	public function get_manager(): banner
	{
		return new banner(
			$this->files_upload,
			$this->filesystem,
			$this->root_path
		);
	}
}
