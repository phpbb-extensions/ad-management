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
	static public function depends_on()
	{
		return array('\phpbb\ads\migrations\v10x\m1_initial_schema');
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
//			'add_index'	=> array(
//				$this->table_prefix . 'ads'	=> array(
//					'ad_views'			=> array('ad_views'), // index used in ad\manager::get_ads
//					'ad_clicks'			=> array('ad_clicks'), // index used in ad\manager::get_ads
//					'ad_views_limit'	=> array('ad_views_limit'), // index used in ad\manager::get_ads
//					'ad_clicks_limit'	=> array('ad_clicks_limit'), // index used in ad\manager::get_ads
//				),
//			),
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
//			'drop_keys'	=> array(
//				$this->table_prefix . 'ads' => array(
//					'ad_clicks_limit',
//					'ad_views_limit',
//					'ad_clicks',
//					'ad_views',
//				),
//			),
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
}
