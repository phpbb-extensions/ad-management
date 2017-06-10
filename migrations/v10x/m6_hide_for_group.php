<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\admanagement\migrations\v10x;

class m6_hide_for_group extends \phpbb\db\migration\migration
{
	/**
	* {@inheritDoc}
	*/
	public function effectively_installed()
	{
		return isset($this->config['phpbb_admanagement_hide_groups']);
	}

	/**
	* {@inheritDoc}
	*/
	static public function depends_on()
	{
		return array('\phpbb\admanagement\migrations\v10x\m5_end_date');
	}

	/**
	* Add the ACP settings module
	*
	* @return array Array of data update instructions
	*/
	public function update_data()
	{
		return array(
			array('config.add', array('phpbb_admanagement_hide_groups', '')),

			array('module.add', array(
				'acp',
				'ACP_ADMANAGEMENT_TITLE',
				array(
					'module_basename'	=> '\phpbb\admanagement\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
