<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\admanagement\controller;

/**
* Admin controller
*/
class admin_controller
{
	const MAX_NAME_LENGTH = 255;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\admanagement\ad\manager */
	protected $manager;

	/** @var \phpbb\admanagement\location\manager */
	protected $location_manager;

	/** @var string php_ext */
	protected $php_ext;

	/** @var string ext_path */
	protected $ext_path;

	/** @var string Custom form action */
	protected $u_action;

	/** @var array Form validation errors */
	protected $errors = array();

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface		$db					DB driver interface
	* @param \phpbb\template\template				$template			Template object
	* @param \phpbb\user							$user				User object
	* @param \phpbb\request\request					$request			Request object
	* @param \phpbb\admanagement\ad\manager			$manager			Advertisement manager object
	* @param \phpbb\admanagement\location\manager	$location_manager	Template location manager object
	* @param string									$php_ext			PHP extension
	* @param string									$ext_path			Path to this extension
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\request\request $request, \phpbb\admanagement\ad\manager $manager, \phpbb\admanagement\location\manager $location_manager, $php_ext, $ext_path)
	{
		$this->db = $db;
		$this->template = $template;
		$this->user = $user;
		$this->request = $request;
		$this->manager = $manager;
		$this->location_manager = $location_manager;
		$this->php_ext = $php_ext;
		$this->ext_path = $ext_path;
	}

	/**
	* Process user request
	*
	* @return void
	*/
	public function main()
	{
		$this->user->add_lang_ext('phpbb/admanagement', 'acp');

		// Trigger specific action
		$action = $this->request->variable('action', '');
		if (in_array($action, array('add', 'edit', 'enable', 'disable', 'delete')))
		{
			$this->{'action_' . $action}();
		}

		// Otherwise default to this
		$this->list_ads();
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return void
	*/
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

	/**
	* Get ACP page title for Ads module
	*
	* @return string	Language string for Ads ACP module
	*/
	public function get_page_title()
	{
		return $this->user->lang('ACP_ADMANAGEMENT_TITLE');
	}

	/**
	* Add an advertisement
	*
	* @return void
	*/
	public function action_add()
	{
		add_form_key('phpbb/admanagement/add');
		if ($this->request->is_set_post('submit'))
		{
			$data = $this->get_form_data();

			$this->validate($data, 'phpbb/admanagement/add');

			if (empty($this->errors))
			{
				$ad_id = $this->manager->insert_ad($data);
				$this->manager->insert_ad_locations($ad_id, $data['ad_locations']);

				$this->success('ACP_AD_ADD_SUCCESS');
			}

			$this->assign_locations($data);
			$this->assign_form_data($data);
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ADD_AD'	=> true,
			'U_BACK'	=> $this->u_action,
		));
		$this->assign_locations();
	}

	/**
	* Edit an advertisement
	*
	* @return void
	*/
	public function action_edit()
	{
		$ad_id = $this->request->variable('id', 0);

		add_form_key('phpbb/admanagement/edit/' . $ad_id);
		if ($this->request->is_set_post('submit'))
		{
			$data = $this->get_form_data();

			$this->validate($data, 'phpbb/admanagement/edit/' . $ad_id);

			if (empty($this->errors))
			{
				$this->manager->delete_ad_locations($ad_id);
				$success = $this->manager->update_ad($ad_id, $data);

				if ($success)
				{
					// Only insert new ad locations to DB when ad exists
					$this->manager->insert_ad_locations($ad_id, $data['ad_locations']);

					$this->success('ACP_AD_EDIT_SUCCESS');
				}
				$this->error('ACP_AD_DOES_NOT_EXIST');
			}
		}
		else
		{
			// Load ad data
			$data = $this->manager->get_ad($ad_id);
			if (empty($data))
			{
				$this->error('ACP_AD_DOES_NOT_EXIST');
			}

			// Load ad template locations
			$data['ad_locations'] = $this->manager->get_ad_locations($ad_id);
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_EDIT_AD'	=> true,
			'EDIT_ID'	=> $ad_id,
			'U_BACK'	=> $this->u_action,
		));
		$this->assign_locations($data);
		$this->assign_form_data($data);
	}

	/**
	* Enable an advertisement
	*
	* @return void
	*/
	public function action_enable()
	{
		$this->ad_enable(true);
	}

	/**
	* Disable an advertisement
	*
	* @return void
	*/
	public function action_disable()
	{
		$this->ad_enable(false);
	}

	/**
	* Delete an advertisement
	*
	* @return void
	*/
	public function action_delete()
	{
		$ad_id = $this->request->variable('id', 0);
		if ($ad_id)
		{
			if (confirm_box(true))
			{
				$this->manager->delete_ad_locations($ad_id);
				$success = $this->manager->delete_ad($ad_id);

				// Only notify user on error or if not ajax
				if (!$success)
				{
					$this->error('ACP_AD_DELETE_ERRORED');
				}
				else if (!$this->request->is_ajax())
				{
					$this->success('ACP_AD_DELETE_SUCCESS');
				}
			}
			else
			{
				confirm_box(false, $this->user->lang('CONFIRM_OPERATION'), build_hidden_fields(array(
					'id'		=> $ad_id,
					'i'			=> $this->request->variable('i', ''),
					'mode'		=> $this->request->variable('mode', ''),
					'action'	=> 'delete'
				)));
			}
		}
	}

	/**
	* Display the ads
	*
	* @return void
	*/
	public function list_ads()
	{
		foreach ($this->manager->get_all_ads() as $row)
		{
			$ad_enabled = (int) $row['ad_enabled'];

			$this->template->assign_block_vars('ads', array(
				'NAME'		=> $row['ad_name'],
				'S_ENABLED'	=> $ad_enabled,
				'U_ENABLE'	=> $this->u_action . '&amp;action=' . ($ad_enabled ? 'disable' : 'enable') . '&amp;id=' . $row['ad_id'],
				'U_PREVIEW'	=> append_sid(generate_board_url() . '/index.' . $this->php_ext, 'ad_preview=' . $row['ad_id']),
				'U_EDIT'	=> $this->u_action . '&amp;action=edit&amp;id=' . $row['ad_id'],
				'U_DELETE'	=> $this->u_action . '&amp;action=delete&amp;id=' . $row['ad_id'],
			));
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'U_ACTION_ADD'	=> $this->u_action . '&amp;action=add',
			'ICON_PREVIEW'	=> '<img src="' . $this->ext_path . 'adm/images/icon_preview.png" alt="' . $this->user->lang('AD_PREVIEW') . '" title="' . $this->user->lang('AD_PREVIEW') . '" />',
		));
	}

	/**
	* Enable/disable an advertisement
	*
	* @param	bool	$enable	Enable or disable the advertisement?
	* @return void
	*/
	protected function ad_enable($enable)
	{
		$ad_id = $this->request->variable('id', 0);

		$success = $this->manager->update_ad($ad_id, array(
			'ad_enabled'	=> (int) $enable,
		));

		// If AJAX was used, show user a result message
		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send(array(
				'text'	=> $this->user->lang($enable ? 'ENABLED' : 'DISABLED'),
				'title'	=> $this->user->lang('AD_ENABLE_TITLE', (int) $enable),
			));
		}

		// Otherwise, show traditional infobox
		if ($success)
		{
			$this->success($enable ? 'ACP_AD_ENABLE_SUCCESS' : 'ACP_AD_DISABLE_SUCCESS');
		}
		else
		{
			$this->error($enable ? 'ACP_AD_ENABLE_ERRORED' : 'ACP_AD_DISABLE_ERRORED');
		}
	}

	/**
	* Get admin form data.
	*
	* @return	array	Form data
	*/
	protected function get_form_data()
	{
		return array(
			'ad_name'		=> $this->request->variable('ad_name', '', true),
			'ad_note'		=> $this->request->variable('ad_note', '', true),
			'ad_code'		=> $this->request->variable('ad_code', '', true),
			'ad_enabled'	=> $this->request->variable('ad_enabled', 0),
			'ad_locations'	=> $this->request->variable('ad_locations', array('')),
		);
	}

	/**
	* Validate form data.
	*
	* @param	array	$data		The form data.
	* @param	string	$form_name	The form name.
	* @return void
	*/
	protected function validate($data, $form_name)
	{
		if (!check_form_key($form_name))
		{
			$this->errors[] = $this->user->lang('FORM_INVALID');
		}

		if ($data['ad_name'] === '')
		{
			$this->errors[] = $this->user->lang('AD_NAME_REQUIRED');
		}
		if (truncate_string($data['ad_name'], self::MAX_NAME_LENGTH) !== $data['ad_name'])
		{
			$this->errors[] = $this->user->lang('AD_NAME_TOO_LONG', self::MAX_NAME_LENGTH);
		}
	}

	/**
	* Assign form data to the template.
	*
	* @param	array	$data	The form data.
	* @return void
	*/
	protected function assign_form_data($data)
	{
		$this->template->assign_vars(array(
			'S_ERROR'		=> (bool) count($this->errors),
			'ERROR_MSG'		=> count($this->errors) ? implode('<br />', $this->errors) : '',

			'AD_NAME'		=> $data['ad_name'],
			'AD_NOTE'		=> $data['ad_note'],
			'AD_CODE'		=> $data['ad_code'],
			'AD_ENABLED'	=> $data['ad_enabled'],
		));
	}

	/**
	* Assign template locations data to the template.
	*
	* @param	mixed	$data	The form data or nothing.
	* @return	void
	*/
	protected function assign_locations($data = false)
	{
		foreach ($this->location_manager->get_all_locations() as $location_id => $location_data)
		{
			$this->template->assign_block_vars('ad_locations', array(
				'LOCATION_ID'	=> $location_id,
				'LOCATION_DESC'	=> $location_data['desc'],
				'LOCATION_NAME'	=> $location_data['name'],
				'S_SELECTED'	=> $data ? in_array($location_id, $data['ad_locations']) : false,
			));
		}
	}

	/**
	* Print success message.
	*
	* It takes arguments in the form of a language key, followed by language substitution values.
	*/
	protected function success()
	{
		trigger_error(call_user_func_array(array($this->user, 'lang'), func_get_args()) . adm_back_link($this->u_action));
	}

	/**
	* Print error message.
	*
	* It takes arguments in the form of a language key, followed by language substitution values.
	*/
	protected function error()
	{
		trigger_error(call_user_func_array(array($this->user, 'lang'), func_get_args()) . adm_back_link($this->u_action), E_USER_WARNING);
	}
}
