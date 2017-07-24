<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\migrations\v10x;

class m12_banner_dir extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritDoc}
	 */
	public function effectively_installed()
	{
		return file_exists($this->phpbb_root_path . 'images/phpbb_ads');
	}

	/**
	 * {@inheritDoc}
	 */
	static public function depends_on()
	{
		return array(
			'\phpbb\ads\migrations\v10x\m1_initial_schema',
		);
	}

	/**
	 * Execute create_banner_dir() method
	 *
	 * @return array Array of data update instructions
	 */
	public function update_data()
	{
		return array(
			array('custom', array(array(&$this, 'create_banner_dir'))),
		);
	}

	/**
	 * Create images/phpbb_ads directory
	 *
	 * @return void
	 */
	public function create_banner_dir()
	{
		mkdir($this->phpbb_root_path . 'images/phpbb_ads', 0777, true);
	}
}
