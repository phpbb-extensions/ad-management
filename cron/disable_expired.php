<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\cron;

class disable_expired extends \phpbb\cron\task\base
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\ads\ad\manager */
	protected $manager;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config $config Config object
	 * @param \phpbb\ads\ad\manager $manager Advertisement manager
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\ads\ad\manager $manager)
	{
		$this->config = $config;
		$this->manager = $manager;
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$this->manager->disable_expired_ads();
		$this->config->set('phpbb_ads_expiration_last_gc', time(), false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function should_run()
	{
		return (int) $this->config['phpbb_ads_expiration_last_gc'] < strtotime('1 hour ago');
	}
}
