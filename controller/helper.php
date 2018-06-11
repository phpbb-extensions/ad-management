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
 * Helper
 */
class helper
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\ads\ad\manager */
	protected $manager;

	/** @var \phpbb\ads\location\manager */
	protected $location_manager;

	/** @var \phpbb\group\helper */
	protected $group_helper;

	/** @var string root_path */
	protected $root_path;

	/** @var string php_ext */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\user                 $user             User object
	 * @param \phpbb\user_loader          $user_loader      User loader object
	 * @param \phpbb\language\language    $language         Language object
	 * @param \phpbb\template\template    $template         Template object
	 * @param \phpbb\log\log              $log              The phpBB log system
	 * @param \phpbb\ads\ad\manager 	  $manager 			Ad manager object
	 * @param \phpbb\ads\location\manager $location_manager Template location manager object
	 * @param \phpbb\group\helper         $group_helper     Group helper object
	 * @param string                      $root_path        phpBB root path
	 * @param string                      $php_ext          PHP extension
	 */
	public function __construct(\phpbb\user $user, \phpbb\user_loader $user_loader, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\log\log $log, \phpbb\ads\ad\manager $manager, \phpbb\ads\location\manager $location_manager, \phpbb\group\helper $group_helper, $root_path, $php_ext)
	{
		$this->user = $user;
		$this->user_loader = $user_loader;
		$this->language = $language;
		$this->template = $template;
		$this->log = $log;
		$this->location_manager = $location_manager;
		$this->manager = $manager;
		$this->group_helper = $group_helper;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Assign ad data for ACP form template.
	 *
	 * @param	array	$data	Ad data
	 * @param	array	$errors	Validation errors
	 */
	public function assign_data($data, $errors)
	{
		$this->assign_locations($data['ad_locations']);
		$this->assign_groups(isset($data['ad_id']) ? $data['ad_id'] : 0);

		$errors = array_map(array($this->language, 'lang'), $errors);
		$this->template->assign_vars(array(
			'S_ERROR'   => (bool) count($errors),
			'ERROR_MSG' => count($errors) ? implode('<br />', $errors) : '',

			'AD_NAME'         	=> $data['ad_name'],
			'AD_NOTE'         	=> $data['ad_note'],
			'AD_CODE'         	=> $data['ad_code'],
			'AD_ENABLED'      	=> $data['ad_enabled'],
			'AD_END_DATE'     	=> $data['ad_end_date'],
			'AD_PRIORITY'     	=> $data['ad_priority'],
			'AD_CONTENT_ONLY'	=> $data['ad_content_only'],
			'AD_VIEWS_LIMIT'  	=> $data['ad_views_limit'],
			'AD_CLICKS_LIMIT' 	=> $data['ad_clicks_limit'],
			'AD_OWNER'        	=> $this->get_username($data['ad_owner']),
			'AD_CENTERING'      => $data['ad_centering'],
		));
	}

	/**
	 * Assign template locations data to the template.
	 *
	 * @param	mixed	$ad_locations	The form data or nothing.
	 * @return	void
	 */
	public function assign_locations($ad_locations = false)
	{
		foreach ($this->location_manager->get_all_locations() as $location_category_id => $location_category)
		{
			$this->template->assign_block_vars('ad_locations', array(
				'CATEGORY_NAME' => $this->language->lang($location_category_id),
			));

			foreach ($location_category as $location_id => $location_data)
			{
				$this->template->assign_block_vars('ad_locations', array(
					'LOCATION_ID'   => $location_id,
					'LOCATION_DESC' => $location_data['desc'],
					'LOCATION_NAME' => $location_data['name'],
					'S_SELECTED'    => $ad_locations ? in_array($location_id, $ad_locations) : false,
				));
			}
		}
	}

	/**
	 * Assign groups data to the template.
	 *
	 * @param int $ad_id Advertisement ID
	 * @return void
	 */
	public function assign_groups($ad_id = 0)
	{
		$groups = $this->manager->load_groups($ad_id);
		foreach ($groups as $group)
		{
			$this->template->assign_block_vars('groups', array(
				'ID'         => $group['group_id'],
				'NAME'       => $this->group_helper->get_name($group['group_name']),
				'S_SELECTED' => (bool) $group['group_selected'],
			));
		}
	}

	/**
	 * Log action
	 *
	 * @param	string	$action		Performed action in uppercase
	 * @param	string	$ad_name	Advertisement name
	 * @return	void
	 */
	public function log($action, $ad_name)
	{
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_PHPBB_ADS_' . $action . '_LOG', time(), array($ad_name));
	}

	/**
	 * Get "Find username" URL to easily look for ad owner.
	 *
	 * @return	string	Find username URL
	 */
	public function get_find_username_link()
	{
		return append_sid("{$this->root_path}memberlist.{$this->php_ext}", 'mode=searchuser&amp;form=acp_admanagement_add&amp;field=ad_owner&amp;select_single=true');
	}

	/**
	 * Is an ad expired?
	 *
	 * @param	array	$row	Advertisement data
	 * @return	bool	True if expired, false otherwise
	 */
	public function is_expired($row)
	{
		if ((int) $row['ad_end_date'] > 0 && (int) $row['ad_end_date'] < time())
		{
			return true;
		}

		if ($row['ad_views_limit'] && $row['ad_views'] >= $row['ad_views_limit'])
		{
			return true;
		}

		if ($row['ad_clicks_limit'] && $row['ad_clicks'] >= $row['ad_clicks_limit'])
		{
			return true;
		}

		return false;
	}

	/**
	 * Prepare ad owner for display. Method takes user_id
	 * of the ad owner and returns username.
	 *
	 * @param	int		$user_id	User ID
	 * @return	string	Username belonging to $user_id.
	 */
	protected function get_username($user_id)
	{
		if (!$user_id)
		{
			return '';
		}

		$this->user_loader->load_users(array($user_id));
		return $this->user_loader->get_username($user_id, 'username');
	}
}
