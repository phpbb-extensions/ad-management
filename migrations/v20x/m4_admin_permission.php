<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2022 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\migrations\v20x;

/**
* Migration stage 4: Add Admin Permission
*/
class m4_admin_permission extends \phpbb\db\migration\container_aware_migration
{
	/**
	 * {@inheritdoc
	 */
	public function effectively_installed()
	{
		$sql = 'SELECT * FROM ' . $this->table_prefix . "acl_options
			WHERE auth_option = 'a_phpbb_ads_m' OR auth_option = 'a_phpbb_ads_s'";
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row !== false;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return [
			'\phpbb\ads\migrations\v10x\m1_initial_schema',
			'\phpbb\ads\migrations\v10x\m2_acp_module',
			'\phpbb\ads\migrations\v20x\m3_add_start_date',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function update_data()
	{
		return [
			// Add permission
			['permission.add', ['a_phpbb_ads_m', true]],
			['permission.add', ['a_phpbb_ads_s', true]],

			// Set permissions
			['if', [
				['permission.role_exists', ['ROLE_ADMIN_FULL']],
				['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_phpbb_ads_m']],
			]],
			['if', [
				['permission.role_exists', ['ROLE_ADMIN_FULL']],
				['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_phpbb_ads_s']],
			]],
			['if', [
				['permission.role_exists', ['ROLE_ADMIN_STANDARD']],
				['permission.permission_set', ['ROLE_ADMIN_STANDARD', 'a_phpbb_ads_m']],
			]],
			['if', [
				['permission.role_exists', ['ROLE_ADMIN_STANDARD']],
				['permission.permission_set', ['ROLE_ADMIN_STANDARD', 'a_phpbb_ads_s']],
			]],

			// Update module auth
			['custom', [[$this, 'update_acp_module_auth']]],
		];
	}

	/**
	 * Update module auth manually, because "module.remove" tool causes problems when deleting extension.
	 */
	public function update_acp_module_auth()
	{
		$sql = 'UPDATE ' . $this->container->getParameter('tables.modules') . "
			SET module_auth = 'ext_phpbb/ads && acl_a_phpbb_ads_m'
			WHERE module_langname = 'ACP_MANAGE_ADS_TITLE'";
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . $this->container->getParameter('tables.modules') . "
			SET module_auth = 'ext_phpbb/ads && acl_a_phpbb_ads_s'
			WHERE module_langname = 'ACP_ADS_SETTINGS_TITLE'";
		$this->db->sql_query($sql);
	}
}
