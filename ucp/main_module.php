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
 * Advertisement management ACP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main()
	{
		global $phpbb_container;

		/** @var \phpbb\ads\controller\ucp_controller $ucp_controller */
		$ucp_controller = $phpbb_container->get('phpbb.ads.ucp.controller');

		// Make the $u_action url available in the UCP controller
		$ucp_controller->set_page_url($this->u_action);

		// Load a template
		$this->tpl_name = 'ucp_ads_stats';

		// Set the page title for our UCP page
		$this->page_title = $ucp_controller->get_page_title();

		$ucp_controller->main();
	}
}
