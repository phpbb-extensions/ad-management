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

use phpbb\filesystem\filesystem;
use phpbb\storage\provider\local;

class m1_storage extends \phpbb\db\migration\container_aware_migration
{
	/**
	 * {@inheritdoc}
	 */
	public function effectively_installed()
	{
		/** @var filesystem $filesystem_interface */
		$filesystem = $this->container->get('filesystem');

		return $this->config->offsetExists('storage\\phpbb_ads\\provider') &&
			$this->config->offsetExists('storage\\phpbb_ads\\config\\path') &&
			$filesystem->exists($this->phpbb_root_path . 'images/phpbb_ads');
	}

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
			['config.add', ['storage\\phpbb_ads\\config\\path', 'images/phpbb_ads']],
			['custom', [[$this, 'migrate_ads_storage']]],
		);
	}

	public function migrate_ads_storage()
	{
		/** @var filesystem $filesystem_interface */
		$filesystem = $this->container->get('filesystem');

		/** @var file_tracker $file_tracker */
		$file_tracker = $this->container->get('storage.file_tracker');

		$dir = $this->phpbb_root_path . 'images/phpbb_ads';

		if (!$filesystem->exists($dir))
		{
			$filesystem->mkdir($dir);
		}

		$handle = @opendir($dir);

		if ($handle)
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file === '.' || $file === '..')
				{
					continue;
				}

				$file_tracker->track_file('phpbb_ads', $file, filesize($dir . '/' . $file));
			}

			closedir($handle);
		}
	}

	public function revert_data()
	{
		return [
			['config.remove', ['storage\\phpbb_ads\\provider']],
			['config.remove', ['storage\\phpbb_ads\\config\\path']],
			['custom', [[$this, 'revert_ads_storage']]],
		];
	}

	public function revert_ads_storage()
	{
		$this->sql_query('DELETE FROM ' . $this->tables['storage'] . ' WHERE storage = "phpbb_ads"');
	}
}
