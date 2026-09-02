<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\migrations\v20x;

class m8_text_storage extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return array(
			'\phpbb\ads\migrations\v10x\m1_initial_schema',
			'\phpbb\ads\migrations\v20x\m7_per_ad_tracking',
		);
	}

	/**
	 * Use portable Unicode and unbounded text column types.
	 *
	 * @return array Array of table schema changes
	 */
	public function update_schema()
	{
		return array(
			'change_columns' => array(
				$this->table_prefix . 'ads' => array(
					'ad_name' => array('VCHAR_UNI:255', ''),
					'ad_code' => array('MTEXT_UNI', ''),
				),
			),
		);
	}

	/**
	 * Keep the widened columns until the initial schema migration drops the table.
	 * Narrowing them could fail when existing data no longer fits the old types.
	 *
	 * @return array Array of table schema changes
	 */
	public function revert_schema()
	{
		return array();
	}
}
