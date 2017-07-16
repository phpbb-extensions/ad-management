<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\controller;

/**
 * Front controller
 */
class ucp_controller
{
	/** @var \phpbb\ads\ad\manager */
	protected $manager;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor
	 *
	 * @param \phpbb\ads\ad\manager		$manager 	Advertisement manager object
	 * @param \phpbb\user				$user		User object
	 * @param \phpbb\template\template	$template	Template object
	 */
	public function __construct(\phpbb\ads\ad\manager $manager, \phpbb\user $user, \phpbb\template\template $template)
	{
		$this->manager = $manager;
		$this->user = $user;
		$this->template = $template;
	}

	/**
	 * @param	string	$u_action	Action URL
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

	/**
	 * @return	string	Module language string
	 */
	public function get_page_title()
	{
		return $this->user->lang('UCP_PHPBB_ADS_STATS');
	}

	/**
	 * Display UCP ads module
	 */
	public function main()
	{
		foreach ($this->manager->get_all_ads($this->user->data['user_id']) as $ad)
		{
			$this->template->assign_block_vars('ads', array(
				'NAME'		=> $ad['ad_name'],
				'VIEWS'		=> $ad['ad_views'],
				'CLICKS'	=> $ad['ad_clicks'],
			));
		}
	}
}
