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

class m9_views_clicks extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritDoc}
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'ads', 'ad_views');
	}

	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return array(
			'\phpbb\ads\migrations\v10x\m1_initial_schema',
			'\phpbb\ads\migrations\v10x\m4_indexes',
			'\phpbb\ads\migrations\v10x\m5_end_date',
			'\phpbb\ads\migrations\v10x\m8_priority',
		);
	}

	/**
	 * Add the views and clicks to ads table
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'ads' => array(
					'ad_views'			=> array('UINT', 0),
					'ad_clicks'			=> array('UINT', 0),
					'ad_views_limit'	=> array('UINT', 0),
					'ad_clicks_limit'	=> array('UINT', 0),
				),
			),
		);
	}

	/**
	 * Drop the views and clicks from ads table
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'ads' => array(
					'ad_views',
					'ad_clicks',
					'ad_views_limit',
					'ad_clicks_limit',
				),
			),
		);
	}

	/**
	 * Add phpbb_ads_enable_views and phpbb_ads_enable_clicks config
	 *
	 * @return array Array of data update instructions
	 */
	public function update_data()
	{
		return array(
			array('config.add', array('phpbb_ads_enable_views', 0)),
			array('config.add', array('phpbb_ads_enable_clicks', 0)),
		);
	}
}
