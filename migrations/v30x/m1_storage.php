<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\migrations\v30x;

use phpbb\storage\provider\local;

class m1_storage extends \phpbb\db\migration\container_aware_migration
{
	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v400\dev',
			'\phpbb\ads\migrations\v20x\m1_hide_ad_for_group',
		);
	}

	public function update_data()
	{
		return array(
			['config.add', ['storage\\phpbb_ads\\provider', local::class]],
			['config.add', ['storage\\phpbb_ads\\config\\path', 'images/phpbb_ads']], // todo: make sure this exists in migration if is a new installation
		);
	}

	public function revert_data()
	{
		return [
			['config.remove', ['storage\\phpbb_ads\\provider']],
			['config.remove', ['storage\\phpbb_ads\\config\\path']],
		];
	}
}
