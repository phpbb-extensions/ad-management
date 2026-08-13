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

class m7_per_ad_tracking extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritDoc}
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'ads', 'ad_views_enabled')
			&& $this->db_tools->sql_column_exists($this->table_prefix . 'ads', 'ad_clicks_enabled');
	}

	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return array(
			'\phpbb\ads\migrations\v10x\m1_initial_schema',
			'\phpbb\ads\migrations\v10x\m9_views_clicks',
			'\phpbb\ads\migrations\v20x\m6_ad_consent_option',
		);
	}

	/**
	 * Add per-ad tracking options.
	 *
	 * @return array Array of table schema
	 */
	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'ads' => array(
					'ad_views_enabled' => array('BOOL', 0),
					'ad_clicks_enabled' => array('BOOL', 0),
				),
			),
		);
	}

	/**
	 * Remove per-ad tracking options.
	 *
	 * @return array Array of table schema
	 */
	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'ads' => array(
					'ad_views_enabled',
					'ad_clicks_enabled',
				),
			),
		);
	}

	/**
	 * Migrate global tracking settings and remove obsolete configuration.
	 *
	 * @return array Array of data update instructions
	 */
	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'migrate_tracking_settings'))),
			array('config.remove', array('phpbb_ads_enable_views')),
			array('config.remove', array('phpbb_ads_enable_clicks')),
			array('config.add', array('phpbb_ads_expiration_last_gc', 0, true)),
		);
	}

	/**
	 * Copy old global values to every existing advertisement.
	 *
	 * @return void
	 */
	public function migrate_tracking_settings()
	{
		$sql_ary = array(
			'ad_views_enabled' => !empty($this->config['phpbb_ads_enable_views']) ? 1 : 0,
			'ad_clicks_enabled' => !empty($this->config['phpbb_ads_enable_clicks']) ? 1 : 0,
		);

		$sql = 'UPDATE ' . $this->table_prefix . 'ads
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary);
		$this->db->sql_query($sql);
	}
}
