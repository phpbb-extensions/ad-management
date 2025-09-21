<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\migrations\v20x;

/**
* Migration stage 5: Add setting to show privacy agreement
*/
class m5_add_privacy_setting extends \phpbb\db\migration\migration
{
	/**
	 * {@inheritdoc
	 */
	public function effectively_installed()
	{
		$this->config->offsetExists('phpbb_ads_show_agreement');
	}

	/**
	 * {@inheritDoc}
	 */
	public static function depends_on()
	{
		return ['\phpbb\ads\migrations\v20x\m4_admin_permission'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function update_data()
	{
		return [
			['config.add', ['phpbb_ads_show_agreement', 0]],
		];
	}
}
