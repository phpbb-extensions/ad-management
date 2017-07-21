<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\ucp;

/**
 * Advertisement management UCP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\phpbb\ads\ucp\main_module',
			'title'		=> 'UCP_PHPBB_ADS_TITLE',
			'modes'		=> array(
				'stats'	=> array(
					'title'	=> 'UCP_PHPBB_ADS_STATS',
					'auth'	=> 'ext_phpbb/ads',
					'cat'	=> array('UCP_PHPBB_ADS_TITLE')
				),
			),
		);
	}
}
