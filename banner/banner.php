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

use phpbb\storage\storage;

class banner
{
	/** @var \phpbb\files\upload */
	protected $files_upload;

	/** @var \phpbb\files\filespec_storage */
	protected $file;

	/** @var storage */
	protected $storage;

	/**
	 * Constructor
	 *
	 * @param \phpbb\files\upload	$files_upload	Files upload object
	 * @param storage				$storage		Storage object
	 */
	public function __construct(\phpbb\files\upload $files_upload, storage $storage)
	{
		$this->files_upload = $files_upload;
		$this->storage = $storage;
	}

	public function set_file($file)
	{
		$this->file = $file;
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
		$this->set_file($this->files_upload->handle_upload('files.types.form_storage', 'banner'));
		$this->file->clean_filename('unique_ext');

		// Move file to proper location
		if (!$this->file->move_file($this->storage))
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
		$this->file->remove($this->storage);
	}
}
