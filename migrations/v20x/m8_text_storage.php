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
			'\phpbb\ads\migrations\v10x\m9_views_clicks',
			'\phpbb\ads\migrations\v20x\m7_per_ad_tracking',
		);
	}

	/**
	 * Use portable text column types and remove obsolete count limits.
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
			'drop_columns' => array(
				$this->table_prefix . 'ads' => array(
					'ad_views_limit',
					'ad_clicks_limit',
				),
			),
		);
	}

	/**
	 * Restore legacy columns so the released migration which introduced them can
	 * remove them later in the uninstall chain. Widened text columns stay widened.
	 *
	 * @return array Array of table schema changes
	 */
	public function revert_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'ads' => array(
					'ad_views_limit' => array('UINT', 0),
					'ad_clicks_limit' => array('UINT', 0),
				),
			),
		);
	}
}
