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

class alert implements test_interface
{
	/**
	 * {@inheritDoc}
	 *
	 * Javascript alert() test.
	 * This test checks for the presence of alert() in an ad code.
	 * There is no reason why ad would trigger alert, so it's
	 * categorized as warning.
	 */
	public function run($ad_code)
	{
		if (preg_match('/alert\s*\(/U', $ad_code))
		{
			return array(
				'severity'	=> 'warning',
				'message'	=> 'ALERT_USAGE',
			);
		}

		return false;
	}
}
