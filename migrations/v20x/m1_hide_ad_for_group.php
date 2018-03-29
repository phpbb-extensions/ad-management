<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\migrations\v20x;

class m1_hide_ad_for_group extends \phpbb\db\migration\container_aware_migration
{
	/**
	 * {@inheritDoc}
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'ad_group');
	}

	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return array(
			'\phpbb\ads\migrations\v10x\m1_initial_schema',
			'\phpbb\ads\migrations\v10x\m4_indexes',
			'\phpbb\ads\migrations\v10x\m6_hide_for_group',
		);
	}

	/**
	 * Add the ad_group table
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'ad_group'	=> array(
					'COLUMNS'	=> array(
						'ad_id'			=> array('UINT', 0),
						'group_id'		=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> array('ad_id', 'group_id'),
				),
			),
		);
	}

	/**
	 * Drop the ad_group table
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'ad_group',
			),
		);
	}

	/**
	 * Remove phpbb_ads_hide_groups config.
	 *
	 * @return array Array of data update instructions
	 */
	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'convert_hide_groups'))),
			array('config_text.remove', array('phpbb_ads_hide_groups')),
		);
	}

	/**
	 * Convert hide_groups config value into rows in ad_group table
	 */
	public function convert_hide_groups()
	{
		$sql_ary = array();

		$hide_groups = json_decode($this->container->get('config_text')->get('phpbb_ads_hide_groups'), true);
		$sql = 'SELECT ad_id
			FROM ' . $this->table_prefix . 'ads';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			foreach ($hide_groups as $group_id)
			{
				$sql_ary[] = array(
					'ad_id'		=> $row['ad_id'],
					'group_id'	=> $group_id,
				);
			}
		}

		$this->db->sql_multi_insert($this->table_prefix . 'ad_group', $sql_ary);
	}
}
