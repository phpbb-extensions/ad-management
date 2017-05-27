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

	/** @var string ads_table */
	protected $ads_table;

	/** @var string php_ext */
	protected $php_ext;

	/** @var string phpbb_admin_path */
	protected $phpbb_admin_path;

	/** @var string Custom form action */
	protected $u_action;

	/** @var array Form validation errors */
	protected $errors = array();

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface	$db					DB driver interface
	* @param \phpbb\template\template			$template			Template object
	* @param \phpbb\user						$user				User object
	* @param \phpbb\request\request				$request			Request object
	* @param string								$ads_table			Ads table
	* @param string								$php_ext			PHP extension
	* @param string								$phpbb_admin_path	Path to admin
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\request\request $request, $ads_table, $php_ext, $phpbb_admin_path)
	{
		$this->db = $db;
		$this->template = $template;
		$this->user = $user;
		$this->request = $request;
		$this->ads_table = $ads_table;
		$this->php_ext = $php_ext;
		$this->phpbb_admin_path = $phpbb_admin_path;
	}

	/**
	* Process user request
	*
	* @return void
	*/
	public function main()
	{
		$this->user->add_lang_ext('phpbb/admanagement', 'acp');

		switch ($this->request->variable('action', ''))
		{
			case 'add':

				$this->action_add();

			break;

			case 'edit':

				$this->action_edit();

			break;

			case 'enable':

				$this->ad_enable(true);

			break;

			case 'disable':

				$this->ad_enable(false);

			break;

			case 'delete':

				$this->action_delete();

			break;
		}

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
			$this->check_form_key('phpbb/admanagement/add');

			$data = $this->get_form_data();

			$this->validate($data);

			if (empty($this->errors))
			{
				// Insert the ad data to the database
				$sql = 'INSERT INTO ' . $this->ads_table . ' ' . $this->db->sql_build_array('INSERT', $data);
				$this->db->sql_query($sql);

				$this->success('ACP_AD_ADD_SUCCESS');
			}
			else
			{
				$this->assign_errors();
				$this->assign_form_data($data);
			}
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ADD_AD'	=> true,
			'U_BACK'	=> $this->u_action,
		));
	}

	/**
	* Edit an advertisement
	*
	* @return void
	*/
	public function action_edit()
	{
		$ad_id = $this->request->variable('id', 0);

		add_form_key('phpbb/admanagement/edit');
		if ($this->request->is_set_post('submit'))
		{
			$this->check_form_key('phpbb/admanagement/edit');

			$data = $this->get_form_data();

			$this->validate($data);

			if (empty($this->errors))
			{
				// Insert the ad data to the database
				$sql = 'UPDATE ' . $this->ads_table . '
					SET ' . $this->db->sql_build_array('UPDATE', $data) . '
					WHERE ad_id = ' . (int) $ad_id;
				$this->db->sql_query($sql);

				$this->success('ACP_AD_EDIT_SUCCESS');
			}
			else
			{
				$this->assign_errors();
			}
		}
		else
		{
			$sql = 'SELECT *
				FROM ' . $this->ads_table . '
				WHERE ad_id = ' . (int) $ad_id;
			$result = $this->db->sql_query($sql);
			$data = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (empty($data))
			{
				$this->error('ACP_AD_DOES_NOT_EXIST');
			}
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_EDIT_AD'	=> true,
			'EDIT_ID'	=> $ad_id,
			'U_BACK'	=> $this->u_action,
		));
		$this->assign_form_data($data);
	}

	/**
	* Enable/disable an advertisement
	*
	* @param	bool	$enable	Enable or disable the advertisement?
	* @return void
	*/
	public function ad_enable($enable)
	{
		$sql = 'UPDATE ' . $this->ads_table . '
			SET ad_enabled = ' . (int) $enable . '
			WHERE ad_id = ' . (int) $this->request->variable('id', 0);
		$this->db->sql_query($sql);
		$success = (bool) $this->db->sql_affectedrows();

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
				$sql = 'DELETE FROM ' . $this->ads_table . '
					WHERE ad_id = ' . (int) $ad_id;
				$this->db->sql_query($sql);

				// Only notify user on error or if not ajax
				if (!$this->db->sql_affectedrows())
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
		$sql = 'SELECT ad_id, ad_name, ad_enabled
			FROM ' . $this->ads_table;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
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
		$this->db->sql_freeresult($result);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'U_ACTION_ADD'	=> $this->u_action . '&amp;action=add',
			'ICON_PREVIEW'	=> '<img src="' . htmlspecialchars($this->phpbb_admin_path) . 'images/file_up_to_date.gif" alt="' . $this->user->lang('AD_PREVIEW') . '" title="' . $this->user->lang('AD_PREVIEW') . '" />',
		));
	}

	/**
	* Check the form key.
	*
	* @param	string	$form_name	The name of the form.
	* @return void
	*/
	protected function check_form_key($form_name)
	{
		if (!check_form_key($form_name))
		{
			$this->errors[] = $this->user->lang('FORM_INVALID');
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
			'ad_enabled'	=> $this->request->variable('ad_enabled', false),
		);
	}

	/**
	* Validate form data.
	*
	* @param	array	$data	The form data.
	* @return void
	*/
	protected function validate($data)
	{
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
	* Assign errors to the template.
	*
	* @return void
	*/
	protected function assign_errors()
	{
		$this->template->assign_vars(array(
			'S_ERROR'			=> (bool) count($this->errors),
			'ERROR_MSG'			=> count($this->errors) ? implode('<br />', $this->errors) : '',
		));
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
			'AD_NAME'		=> $data['ad_name'],
			'AD_NOTE'		=> $data['ad_note'],
			'AD_CODE'		=> $data['ad_code'],
			'AD_ENABLED'	=> $data['ad_enabled'],
		));
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
