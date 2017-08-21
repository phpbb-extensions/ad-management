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

/**
* Migration stage 1: Initial schema
*/
class m1_initial_schema extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritDoc}
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'ads');
	}

	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v316');
	}

	/**
	 * Add the ads table schema to the database:
	 *	ads:
	 *		ad_id
	 *		ad_name
	 *		ad_note
	 *		ad_code
	 *		ad_enabled
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'ads'	=> array(
					'COLUMNS'	=> array(
						'ad_id'			=> array('UINT', null, 'auto_increment'),
						'ad_name'		=> array('VCHAR:255', ''),
						'ad_note'		=> array('MTEXT_UNI', ''),
						'ad_code'		=> array('TEXT_UNI', ''),
						'ad_enabled'	=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'ad_id',
				),
			),
		);
	}

	/**
	 * Drop the ads table schema from the database
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'ads',
			),
		);
	}
}
