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

use \phpbb\ads\controller\admin_input as input;

/**
 * Admin helper
 */
class admin_helper
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\ads\location\manager */
	protected $location_manager;

	/** @var string root_path */
	protected $root_path;

	/** @var string php_ext */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\user						$user				User object
	 * @param \phpbb\template\template			$template			Template object
	 * @param \phpbb\log\log					$log				The phpBB log system
	 * @param \phpbb\ads\location\manager		$location_manager	Template location manager object
	 * @param string							$root_path			phpBB root path
	 * @param string							$php_ext			PHP extension
	 */
	public function __construct(\phpbb\user $user, \phpbb\template\template $template, \phpbb\log\log $log, \phpbb\ads\location\manager $location_manager, $root_path, $php_ext)
	{
		$this->user = $user;
		$this->template = $template;
		$this->log = $log;
		$this->location_manager = $location_manager;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Assign template locations data to the template.
	 *
	 * @param	mixed	$ad_locations	The form data or nothing.
	 * @return	void
	 */
	public function assign_locations($ad_locations = false)
	{
		foreach ($this->location_manager->get_all_locations() as $location_id => $location_data)
		{
			$this->template->assign_block_vars('ad_locations', array(
				'LOCATION_ID'   => $location_id,
				'LOCATION_DESC' => $location_data['desc'],
				'LOCATION_NAME' => $location_data['name'],
				'S_SELECTED'    => $ad_locations ? in_array($location_id, $ad_locations) : false,
			));
		}
	}

	/**
	 * Assign form data to the template.
	 *
	 * @param	array	$data	The form data.
	 * @return	void
	 */
	public function assign_form_data($data)
	{
		$this->template->assign_vars(array(
			'AD_NAME'         => $data['ad_name'],
			'AD_NOTE'         => $data['ad_note'],
			'AD_CODE'         => $data['ad_code'],
			'AD_ENABLED'      => $data['ad_enabled'],
			'AD_END_DATE'     => $this->prepare_end_date($data['ad_end_date']),
			'AD_PRIORITY'     => $data['ad_priority'],
			'AD_VIEWS_LIMIT'  => $data['ad_views_limit'],
			'AD_CLICKS_LIMIT' => $data['ad_clicks_limit'],
			'AD_OWNER'        => $this->prepare_ad_owner($data['ad_owner']),
		));
	}

	public function assign_errors(array $errors)
	{
		$errors = array_map(array($this->user, 'lang'), $errors);

		$this->template->assign_vars(array(
			'S_ERROR'   => (bool) count($errors),
			'ERROR_MSG' => count($errors) ? implode('<br />', $errors) : '',
		));
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

	public function get_find_username_link()
	{
		return append_sid("{$this->root_path}memberlist.{$this->php_ext}", 'mode=searchuser&amp;form=acp_admanagement_add&amp;field=ad_owner&amp;select_single=true');
	}


	/**
	 * Prepare end date for display
	 *
	 * @param	mixed	$end_date	End date.
	 * @return	string	End date prepared for display.
	 */
	protected function prepare_end_date($end_date)
	{
		if (empty($end_date))
		{
			return '';
		}

		if (is_numeric($end_date))
		{
			return $this->user->format_date($end_date, input::DATE_FORMAT);
		}

		return (string) $end_date;
	}

	/**
	 * Prepare ad owner for display. Method takes user_id
	 * of the ad owner and returns his/her username.
	 *
	 * @param	int		$ad_owner	User ID
	 * @return	string	Username belonging to $ad_owner.
	 */
	protected function prepare_ad_owner($ad_owner)
	{
		// Returns false when no errors occur trying to find the user
		if (false === user_get_id_name($ad_owner, $ad_owner_name))
		{
			if (empty($ad_owner_name))
			{
				return $ad_owner[0];
			}
			return $ad_owner_name[(int) $ad_owner[0]];
		}
		return '';
	}
}
