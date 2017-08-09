<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\analyser\test;

class untrusted_connection implements test_interface
{
	/** @var \phpbb\request\request */
	protected $request;

	/**
	 * Construct an ad code analysis manager object
	 *
	 * @param \phpbb\request\request $request Request object
	 */
	public function __construct(\phpbb\request\request $request)
	{
		$this->request = $request;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Untrusted connection test.
	 * When board runs on HTTPS and ad tries to load a file from
	 * HTTP source, browser throws a warning. We should prevent that.
	 */
	public function run($ad_code)
	{
		$is_https = $this->request->server('HTTPS', false);
		if ($is_https && preg_match('/http[^s]/', $ad_code))
		{
			return array(
				'severity'	=> 'warning',
				'message'	=> 'UNSECURE_CONNECTION',
			);
		}

		return false;
	}
}
