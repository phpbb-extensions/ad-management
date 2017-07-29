<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\banner;

class banner
{
	/** @var \phpbb\files\upload */
	protected $files_upload;

	/** @var \phpbb\filesystem\filesystem_interface */
	protected $filesystem;

	/** @var string */
	protected $root_path;

	/** @var \phpbb\files\filespec */
	protected $file;

	/**
	 * Constructor
	 *
	 * @param \phpbb\files\upload						$files_upload	Files upload object
	 * @param \phpbb\filesystem\filesystem_interface	$filesystem		Filesystem object
	 * @param string									$root_path		Root path
	 */
	public function __construct(\phpbb\files\upload $files_upload, \phpbb\filesystem\filesystem_interface $filesystem, $root_path)
	{
		$this->files_upload = $files_upload;
		$this->filesystem = $filesystem;
		$this->root_path = $root_path;
	}

	public function set_file($file)
	{
		$this->file = $file;
	}

	/**
	 * Create storage directory for banners uploaded by Ads Management
	 *
	 * @throws \phpbb\filesystem\exception\filesystem_exception
	 */
	public function create_storage_dir()
	{
		if (!$this->filesystem->exists($this->root_path . 'images/phpbb_ads'))
		{
			$this->filesystem->mkdir($this->root_path . 'images/phpbb_ads');
		}
	}

	/**
	 * Handle banner upload
	 *
	 * @throws	\phpbb\exception\runtime_exception
	 * @return	string	Filename
	 */
	public function upload()
	{
		// Set file restrictions
		$this->files_upload->reset_vars();
		$this->files_upload->set_allowed_extensions(array('gif', 'jpg', 'jpeg', 'png'));

		// Upload file
		$this->set_file($this->files_upload->handle_upload('files.types.form', 'banner'));
		$this->file->clean_filename('unique_ext');

		// Move file to proper location
		if (!$this->file->move_file('images/phpbb_ads'))
		{
			$this->file->set_error('FILE_MOVE_UNSUCCESSFUL');
		}

		if (count($this->file->error))
		{
			throw new \phpbb\exception\runtime_exception($this->file->error[0]);
		}

		return $this->file->get('realname');
	}

	/**
	 * Remove file from the filesystem
	 */
	public function remove()
	{
		$this->file->remove();
	}
}
