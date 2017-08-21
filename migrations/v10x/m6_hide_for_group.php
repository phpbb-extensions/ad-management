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

class m6_hide_for_group extends \phpbb\db\migration\container_aware_migration
{
	/**
	 * {@inheritDoc}
	 */
	public function effectively_installed()
	{
		$config_text = $this->container->get('config_text');

		return $config_text->get('phpbb_ads_hide_groups') !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return array(
			'\phpbb\ads\migrations\v10x\m1_initial_schema',
			'\phpbb\ads\migrations\v10x\m2_acp_module',
		);
	}

	/**
	 * Add the ACP settings module
	 *
	 * @return array Array of data update instructions
	 */
	public function update_data()
	{
		return array(
			array('config_text.add', array('phpbb_ads_hide_groups', '[]')),

			array('module.add', array(
				'acp',
				'ACP_PHPBB_ADS_TITLE',
				array(
					'module_basename'	=> '\phpbb\ads\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
