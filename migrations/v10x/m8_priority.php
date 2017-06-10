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

class m8_priority extends \phpbb\db\migration\migration
{
	/**
	* {@inheritDoc}
	*/
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'ads', 'ad_priority');
	}

	/**
	* {@inheritDoc}
	*/
	static public function depends_on()
	{
		return array('\phpbb\ads\migrations\v10x\m1_initial_schema');
	}

	/**
	* Add the priority to ads table
	*
	* @return array Array of table schema
	* @access public
	*/
	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'ads' => array(
					'ad_priority' => array('TINT:2', 5),
				),
			),
			'add_index'	=> array(
				$this->table_prefix . 'ads'	=> array(
					'ad_priority'	=> array('ad_priority'), // index used in ad\manager::get_ads
				),
			),
		);
	}

	/**
	* Drop the priority from ads table
	*
	* @return array Array of table schema
	* @access public
	*/
	public function revert_schema()
	{
		return array(
			'drop_keys'	=> array(
				$this->table_prefix . 'ads' => array(
					'ad_priority',
				),
			),
			'drop_columns' => array(
				$this->table_prefix . 'ads' => array(
					'ad_priority',
				),
			),
		);
	}
}
