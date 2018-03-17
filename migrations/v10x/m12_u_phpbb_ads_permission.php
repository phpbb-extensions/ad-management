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
			array('custom', array(array($this, 'set_u_phpbb_ads_permission'))),
			array('custom', array(array($this, 'update_ucp_module_permission'))),
		);
	}

	/**
	 * Find existing ad owners and assign them the new u_phpbb_ads permission
	 */
	public function set_u_phpbb_ads_permission()
	{
		if (!class_exists('auth_admin'))
		{
			include($this->phpbb_root_path . 'includes/acp/auth.' . $this->php_ext);
		}
		$auth_admin = new \auth_admin();

		$sql = 'SELECT ad_owner
			FROM ' . $this->table_prefix . 'ads
			WHERE ad_owner <> 0
			GROUP BY ad_owner';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$auth_admin->acl_set('user', 0, $row['ad_owner'], array('u_phpbb_ads' => 1));
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Update module auth manually, because "module.remove" tool causes problems when deleting extension.
	 */
	public function update_ucp_module_permission()
	{
		$sql = 'UPDATE ' . $this->container->getParameter('tables.modules') . "
			SET module_auth = 'ext_phpbb/ads && acl_u_phpbb_ads'
			WHERE module_langname = 'UCP_PHPBB_ADS_STATS'";
		$this->db->sql_query($sql);
	}
}
