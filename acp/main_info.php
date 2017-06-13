<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\acp;

/**
 * Advertisement management ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\phpbb\ads\acp\main_module',
			'title'		=> 'ACP_PHPBB_ADS_TITLE',
			'modes'		=> array(
				'manage'	=> array(
					'title'	=> 'ACP_MANAGE_ADS_TITLE',
					'auth'	=> 'ext_phpbb/ads && acl_a_board',
					'cat'	=> array('ACP_PHPBB_ADS_TITLE')
				),
				'settings'	=> array(
					'title'	=> 'ACP_ADS_SETTINGS_TITLE',
					'auth'	=> 'ext_phpbb/ads && acl_a_board',
					'cat'	=> array('ACP_PHPBB_ADS_TITLE')
				),
			),
		);
	}
}
