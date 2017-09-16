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

class m10_ad_owner_schema extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritDoc}
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'ads', 'ad_owner');
	}

	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return array(
			'\phpbb\ads\migrations\v10x\m1_initial_schema',
			'\phpbb\ads\migrations\v10x\m4_indexes',
			'\phpbb\ads\migrations\v10x\m9_views_clicks',
		);
	}

	/**
	 * Add the ad owner to ads table
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'ads' => array(
					'ad_owner' => array('UINT', 0),
				),
			),
		);
	}

	/**
	 * Drop the ad owner from ads table
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'ads' => array(
					'ad_owner',
				),
			),
		);
	}
}
