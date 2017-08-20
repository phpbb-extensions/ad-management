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

class m7_adblocker extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritDoc}
	 */
	public function effectively_installed()
	{
		return $this->config->offsetExists('phpbb_ads_adblocker_message');
	}

	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return array('\phpbb\ads\migrations\v10x\m1_initial_schema');
	}

	/**
	 * Add phpbb_ads_adblocker_message config
	 *
	 * @return array Array of data update instructions
	 */
	public function update_data()
	{
		return array(
			array('config.add', array('phpbb_ads_adblocker_message', 0)),
		);
	}
}
