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

class script_without_async implements test_interface
{
	/**
	 * {@inheritDoc}
	 *
	 * Synchronously loaded scripts test.
	 * This test looks for scripts that aren't using `async` attribute
	 * to load itself asynchronously. Such scripts slow down page rendering
	 * time and should be made asynchronous.
	 */
	public function run($ad_code)
	{
		if (preg_match_all('/&lt;script(.*)src(.*)&gt;/U', $ad_code, $matches))
		{
			foreach ($matches[1] as $match)
			{
				if (!preg_match('/ async/', $match))
				{
					return array(
						'severity'	=> 'notice',
						'message'	=> 'SCRIPT_WITHOUT_ASYNC',
					);
				}
			}
		}

		return false;
	}
}
