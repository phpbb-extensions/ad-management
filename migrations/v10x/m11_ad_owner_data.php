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

class m11_ad_owner_data extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return array(
			'\phpbb\ads\migrations\v10x\m1_initial_schema',
			'\phpbb\ads\migrations\v10x\m10_ad_owner_schema',
		);
	}

	/**
	 * Add the UCP module and new permission
	 *
	 * @return array Array of data update instructions
	 */
	public function update_data()
	{
		return array(
			array('module.add', array(
				'ucp',
				'',
				'UCP_PHPBB_ADS_TITLE'
			)),
			array('module.add', array(
				'ucp',
				'UCP_PHPBB_ADS_TITLE',
				array(
					'module_basename' => '\phpbb\ads\ucp\main_module',
					'modes'           => array('stats'),
				),
			)),
		);
	}
}
