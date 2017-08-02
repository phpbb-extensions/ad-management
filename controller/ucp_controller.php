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

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor
	 *
	 * @param \phpbb\ads\ad\manager		$manager 	Advertisement manager object
	 * @param \phpbb\user				$user		User object
	 * @param \phpbb\language\language	$language	Language object
	 * @param \phpbb\template\template	$template	Template object
	 * @param \phpbb\config\config		$config		Config object
	 */
	public function __construct(\phpbb\ads\ad\manager $manager, \phpbb\user $user, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\config\config $config)
	{
		$this->manager = $manager;
		$this->user = $user;
		$this->language = $language;
		$this->template = $template;
		$this->config = $config;
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
		return $this->language->lang('UCP_PHPBB_ADS_STATS');
	}

	/**
	 * Display UCP ads module
	 */
	public function main()
	{
		$this->language->add_lang('ucp', 'phpbb/ads');

		foreach ($this->manager->get_ads_by_owner($this->user->data['user_id']) as $ad)
		{
			$this->template->assign_block_vars('ads', array(
				'NAME'		=> $ad['ad_name'],
				'VIEWS'		=> $ad['ad_views'],
				'CLICKS'	=> $ad['ad_clicks'],
			));
		}

		$this->template->assign_vars(array(
			'S_VIEWS_ENABLED'	=> $this->config['phpbb_ads_enable_views'],
			'S_CLICKS_ENABLED'	=> $this->config['phpbb_ads_enable_clicks'],
		));
	}
}
