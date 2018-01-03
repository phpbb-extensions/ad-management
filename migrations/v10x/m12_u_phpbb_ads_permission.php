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

class m12_u_phpbb_ads_permission extends \phpbb\db\migration\container_aware_migration
{
	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return array(
			'\phpbb\ads\migrations\v10x\m1_initial_schema',
			'\phpbb\ads\migrations\v10x\m10_ad_owner_schema',
			'\phpbb\ads\migrations\v10x\m11_ad_owner_data',
		);
	}

	/**
	 * Add new permission
	 *
	 * @return array Array of data update instructions
	 */
	public function update_data()
	{
		return array(
			array('permission.add', array('u_phpbb_ads')),
			array('custom', array(
				array($this, 'set_u_phpbb_ads_permission'),
				array($this, 'update_ucp_module_permission'),
			)),
		);
	}

	public function set_u_phpbb_ads_permission()
	{
		// get u_phpbb_ads ID
		$sql = 'SELECT auth_option_id
				FROM ' . $this->container->getParameter('tables.acl_options') . '
				WHERE auth_option = "u_phpbb_ads"';
		$this->db->sql_query($sql);
		$auth_option_id = $this->db->sql_fetchfield('auth_option_id');

		// set u_phpbb_ads to true for ad owners
		$sql_ary = array();
		$sql = 'SELECT ad_owner
				FROM ' . $this->table_prefix . 'ads
				WHERE ad_owner != 0
				GROUP BY ad_owner';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql_ary[] = array(
				'user_id'			=> (int) $row['ad_owner'],
				'forum_id'			=> 0,
				'auth_option_id'	=> (int) $auth_option_id,
				'auth_setting'		=> 1,
			);
		}
		$this->db->sql_freeresult($result);

		$this->db->sql_multi_insert($this->container->getParameter('tables.acl_users'), $sql_ary);
		$this->container->get('auth')->acl_clear_prefetch();
	}

	/**
	 * Update module auth manually, because "module.remove" tool causes problems when deleting extension.
	 */
	public function update_ucp_module_permission()
	{
		$sql = 'UPDATE ' . $this->container->getParameter('tables.modules') . '
				SET module_auth = "ext_phpbb/ads && acl_u_phpbb_ads"
				WHERE module_langname = "UCP_PHPBB_ADS_STATS"';
		$this->db->sql_query($sql);
	}
}
