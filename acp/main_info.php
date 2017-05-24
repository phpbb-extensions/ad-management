<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\admanagement\acp;

/**
 * Advertisement management ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\phpbb\admanagement\acp\main_module',
			'title'		=> 'ACP_ADMANAGEMENT_TITLE',
			'modes'		=> array(
				'manage'	=> array(
					'title'	=> 'ACP_ADMANAGEMENT_TITLE',
					'auth'	=> 'ext_phpbb/admanagement && acl_a_board',
					'cat'	=> array('ACP_ADMANAGEMENT_TITLE')
				),
			),
		);
	}
}
