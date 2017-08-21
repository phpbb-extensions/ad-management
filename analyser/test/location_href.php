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

class location_href implements test_interface
{
	/**
	 * {@inheritDoc}
	 *
	 * Javascript redirect using window.location.href test.
	 * This test checks for the presence of redirect in an ad code.
	 * There is no reason why ad would redirect user to another page,
	 * so it's categorized as warning.
	 */
	public function run($ad_code)
	{
		if (preg_match('/location\.href(\s)*=/U', $ad_code))
		{
			return array(
				'severity'	=> 'warning',
				'message'	=> 'LOCATION_CHANGE',
			);
		}

		return false;
	}
}
