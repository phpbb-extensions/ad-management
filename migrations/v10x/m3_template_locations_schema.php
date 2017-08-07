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

class m3_template_locations_schema extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritDoc}
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'ad_locations');
	}

	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return array('\phpbb\ads\migrations\v10x\m1_initial_schema');
	}

	/**
	 * Add the ad_locations table schema to the database:
	 *	ad_locations:
	 *		ad_id
	 *		location_id
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'ad_locations'	=> array(
					'COLUMNS'	=> array(
						'ad_id'				=> array('UINT', 0),
						'location_id'		=> array('VCHAR:255', ''),
					),
					'PRIMARY_KEY'	=> array('ad_id', 'location_id'),
				),
			),
		);
	}

	/**
	 * Drop the ad_locations table schema from the database
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'ad_locations',
			),
		);
	}
}
