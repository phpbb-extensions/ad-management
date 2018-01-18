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

class m13_hide_on_noncontent_pages extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritDoc}
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'ads', 'ad_hide_on_noncontent');
	}

	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return array(
			'\phpbb\ads\migrations\v10x\m1_initial_schema',
			'\phpbb\ads\migrations\v10x\m4_indexes',
		);
	}

	/**
	 * Add the hide-on-noncontent to ads table
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'ads' => array(
					'ad_hide_on_noncontent' => array('BOOL', 0),
				),
			),
			'add_index'	=> array(
				$this->table_prefix . 'ads'	=> array(
					'ad_hon'	=> array('ad_hide_on_noncontent'), // index used in ad\manager::get_ads
				),
			),
		);
	}

	/**
	 * Drop the hide-on-noncontent from ads table
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function revert_schema()
	{
		return array(
			'drop_keys'	=> array(
				$this->table_prefix . 'ads' => array(
					'ad_hon',
				),
			),
			'drop_columns' => array(
				$this->table_prefix . 'ads' => array(
					'ad_hide_on_noncontent',
				),
			),
		);
	}
}
