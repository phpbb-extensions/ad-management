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

class m2_acp_module extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritDoc}
	 */
	public function effectively_installed()
	{
		$sql = 'SELECT module_id
			FROM ' . $this->table_prefix . "modules
			WHERE module_class = 'acp'
				AND module_langname = 'ACP_PHPBB_ADS_TITLE'";
		$result = $this->db->sql_query($sql);
		$module_id = (int) $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return array('\phpbb\ads\migrations\v10x\m1_initial_schema');
	}

	/**
	 * Add the ACP module
	 *
	 * @return array Array of data update instructions
	 */
	public function update_data()
	{
		return array(
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_PHPBB_ADS_TITLE'
			)),
			array('module.add', array(
				'acp',
				'ACP_PHPBB_ADS_TITLE',
				array(
					'module_basename'	=> '\phpbb\ads\acp\main_module',
					'modes'				=> array('manage'),
				),
			)),
		);
	}
}
