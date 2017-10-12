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

	/** @var \phpbb\ads\controller\helper */
	protected $helper;

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
	 * @param \phpbb\ads\ad\manager					$manager 	Advertisement manager object
	 * @param \phpbb\ads\controller\helper			$helper		Helper object
	 * @param \phpbb\user							$user		User object
	 * @param \phpbb\language\language				$language	Language object
	 * @param \phpbb\template\template				$template	Template object
	 * @param \phpbb\config\config					$config		Config object
	 */
	public function __construct(\phpbb\ads\ad\manager $manager, \phpbb\ads\controller\helper $helper, \phpbb\user $user, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\config\config $config)
	{
		$this->manager = $manager;
		$this->helper = $helper;
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
			$ad_enabled = (int) $ad['ad_enabled'];
			$ad_expired = $this->helper->is_expired($ad);

			if ($ad_expired && $ad_enabled)
			{
				$ad_enabled = 0;
				$this->manager->update_ad($ad['ad_id'], array('ad_enabled' => 0));
			}

			$this->template->assign_block_vars($ad_expired ? 'expired' : 'ads', array(
				'NAME'			=> $ad['ad_name'],
				'END_DATE'		=> $ad['ad_end_date'],
				'VIEWS'			=> $ad['ad_views'],
				'VIEWS_LIMIT'	=> $ad['ad_views_limit'],
				'CLICKS'		=> $ad['ad_clicks'],
				'CLICKS_LIMIT'	=> $ad['ad_clicks_limit'],
				'S_ENABLED'		=> $ad_enabled,
			));
		}

		$this->template->assign_vars(array(
			'S_VIEWS_ENABLED'	=> $this->config['phpbb_ads_enable_views'],
			'S_CLICKS_ENABLED'	=> $this->config['phpbb_ads_enable_clicks'],
		));
	}
}
