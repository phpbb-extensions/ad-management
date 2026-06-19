<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\analyser\test;

class iframe implements test_interface
{
	/**
	 * {@inheritDoc}
	 *
	 * Iframes test.
	 * This test looks for iframe tags with src attributes. Such scripts could introduce
	 * external trackers and data collectors that could require user consent.
	 */
	public function run($ad_code)
	{
		if (preg_match('/&lt;iframe(?>(?!&gt;).)*?(?<=\s|&quot;)src\s*=\s*&quot;.*?&gt;/is', $ad_code))
		{
			return array(
				'severity'	=> 'notice',
				'message'	=> 'IFRAME_USAGE',
			);
		}

		return false;
	}
}
