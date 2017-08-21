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

class m4_indexes extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritDoc}
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_index_exists($this->table_prefix . 'ads', 'ad_enabled');
	}

	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return array(
			'\phpbb\ads\migrations\v10x\m1_initial_schema',
			'\phpbb\ads\migrations\v10x\m3_template_locations_schema',
		);
	}

	/**
	 * Add the indexes
	 *
	 * @return array Array of altered table schema
	 * @access public
	 */
	public function update_schema()
	{
		return array(
			'add_index'	=> array(
				$this->table_prefix . 'ads'	=> array(
					'ad_enabled'	=> array('ad_enabled'), // index used in ad\manager::get_ads
				),
				$this->table_prefix . 'ad_locations'	=> array(
					'location_id'	=> array('location_id'), // index used in ad\manager::get_ads
				),
			),
		);
	}

	/**
	 * Drop the indexes
	 *
	 * @return array Array of altered table schema
	 * @access public
	 */
	public function revert_schema()
	{
		return array(
			'drop_keys'	=> array(
				$this->table_prefix . 'ad_locations' => array(
					'location_id',
				),
				$this->table_prefix . 'ads' => array(
					'ad_enabled',
				),
			),
		);
	}
}
