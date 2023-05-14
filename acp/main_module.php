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
 * Advertisement management ACP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Main ACP module
	 *
	 * @param int $id
	 * @param string $mode
	 * @throws \Exception
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \phpbb\ads\controller\admin_controller $admin_controller */
		$admin_controller = $phpbb_container->get('phpbb.ads.admin.controller');

		// Make the $u_action url available in the admin controller
		$admin_controller->set_page_url($this->u_action);

		// Load a template from adm/style for our ACP page
		$this->tpl_name = $mode . '_ads';

		// Set the page title for our ACP page
		$this->page_title = 'ACP_PHPBB_ADS_TITLE';

		$admin_controller->{'mode_' . $mode}();
	}
}
