<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads;

class ext extends \phpbb\extension\base
{
	public const DATE_FORMAT = 'Y-m-d';
	/** UTC-12 grace period, allowing an end date to finish in every timezone. */
	public const EXPIRATION_GRACE_PERIOD = 12 * 60 * 60;
	public const MAX_NAME_LENGTH = 255;
	public const DEFAULT_PRIORITY = 5;
	public const AD_BLOCK_MODES = [0, 1, 2];
	public const NOTIFICATION_TYPE_DISABLED = 'phpbb.ads.notification.type.ad_disabled';

	/**
	 * {@inheritdoc}
	 *
	 * Requires phpBB 3.3.2 due to using role_exists check in permission migration.
	 */
	public function is_enableable()
	{
		return phpbb_version_compare(PHPBB_VERSION, '3.3.2', '>=')
			&& phpbb_version_compare(PHPBB_VERSION, '4.0.0-dev', '<');
	}

	/**
	 * Manage notification type during extension lifecycle.
	 *
	 * @param string $method Notification manager method
	 * @return void
	 */
	protected function manage_notifications($method)
	{
		$this->container->get('notification_manager')->{$method}(self::NOTIFICATION_TYPE_DISABLED);
	}

	/**
	 * {@inheritdoc}
	 */
	public function enable_step($old_state)
	{
		if ($old_state === false)
		{
			$this->manage_notifications('enable_notifications');
		}

		return parent::enable_step($old_state);
	}

	/**
	 * {@inheritdoc}
	 */
	public function disable_step($old_state)
	{
		if ($old_state === false)
		{
			$this->manage_notifications('disable_notifications');
		}

		return parent::disable_step($old_state);
	}

	/**
	 * {@inheritdoc}
	 */
	public function purge_step($old_state)
	{
		if ($old_state === false)
		{
			$this->manage_notifications('purge_notifications');
		}

		return parent::purge_step($old_state);
	}
}
