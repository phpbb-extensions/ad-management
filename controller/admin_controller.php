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

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface	$db			DB driver interface
	* @param \phpbb\template\template			$template	Template object
	* @param \phpbb\user						$user		User object
	* @param \phpbb\request\request				$request	Request object
	* @param string								$ads_table	Ads table
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\request\request $request, $ads_table)
	{
		$this->db = $db;
		$this->template = $template;
		$this->user = $user;
		$this->request = $request;
		$this->ads_table = $ads_table;
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
	* Load module-specific language
	*
	* @return void
	*/
	public function load_lang()
	{
		$this->user->add_lang_ext('phpbb/admanagement', 'acp');
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
	* Get action
	*
	* @return string	Ads module action
	*/
	public function get_action()
	{
		return $this->request->variable('action', '');
	}

	/**
	* Process 'add' action
	*
	* @return void
	*/
	public function action_add()
	{
		$errors = array();

		add_form_key('phpbb/admanagement/add');
		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('phpbb/admanagement/add'))
			{
				$errors[] = $this->user->lang('FORM_INVALID');
			}

			$data = array(
				'ad_name'		=> $this->request->variable('ad_name', '', true),
				'ad_note'		=> $this->request->variable('ad_note', '', true),
				'ad_code'		=> $this->request->variable('ad_code', '', true),
				'ad_enabled'	=> $this->request->variable('ad_code', false),
			);

			// Validate data
			if ($data['ad_name'] === '')
			{
				$errors[] = $this->user->lang('AD_NAME_REQUIRED');
			}
			if (truncate_string($data['ad_name'], 255) !== $data['ad_name'])
			{
				$errors[] = $this->user->lang('AD_NAME_TOO_LONG');
			}

			if (empty($errors))
			{
				// Insert the ad data to the database
				$sql = 'INSERT INTO ' . $this->ads_table . ' ' . $this->db->sql_build_array('INSERT', $data);
				$this->db->sql_query($sql);

				trigger_error($this->user->lang('ACP_AD_ADD_SUCCESS') . adm_back_link($this->u_action));
			}
			else
			{
				$this->template->assign_vars(array(
					'S_ERROR'			=> (bool) count($errors),
					'ERROR_MSG'			=> count($errors) ? implode('<br />', $errors) : '',

					'AD_NAME'		=> $data['ad_name'],
					'AD_NOTE'		=> $data['ad_note'],
					'AD_CODE'		=> $data['ad_code'],
					'AD_ENABLED'	=> $data['ad_enabled'],
				));
			}
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ADD_AD'	=> true,
			'U_BACK'	=> $this->u_action,
		));
	}

	/**
	* Process 'edit' action
	*
	* @return void
	*/
	public function action_edit()
	{
		$ad_id = $this->request->variable('id', 0);
		$data = $errors = array();

		add_form_key('phpbb/admanagement/edit');
		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('phpbb/admanagement/edit'))
			{
				$errors[] = $this->user->lang('FORM_INVALID');
			}

			$data = array(
				'ad_name'		=> $this->request->variable('ad_name', '', true),
				'ad_note'		=> $this->request->variable('ad_note', '', true),
				'ad_code'		=> $this->request->variable('ad_code', '', true),
				'ad_enabled'	=> $this->request->variable('ad_enabled', false),
			);

			// Validate data
			if ($data['ad_name'] === '')
			{
				$errors[] = $this->user->lang('AD_NAME_REQUIRED');
			}
			if (truncate_string($data['ad_name'], 255) !== $data['ad_name'])
			{
				$errors[] = $this->user->lang('AD_NAME_TOO_LONG');
			}

			if (empty($errors))
			{
				// Insert the ad data to the database
				$sql = 'UPDATE ' . $this->ads_table . '
					SET ' . $this->db->sql_build_array('UPDATE', $data) . '
					WHERE ad_id = ' . (int) $ad_id;
				$this->db->sql_query($sql);

				trigger_error($this->user->lang('ACP_AD_EDIT_SUCCESS') . adm_back_link($this->u_action));
			}
			else
			{
				$this->template->assign_vars(array(
					'S_ERROR'			=> (bool) count($errors),
					'ERROR_MSG'			=> count($errors) ? implode('<br />', $errors) : '',
				));
			}
		}
		else
		{
			$sql = 'SELECT *
				FROM ' . $this->ads_table . '
				WHERE ad_id = ' . (int) $ad_id;
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset($result);
			$data = $rowset[0];
			$this->db->sql_freeresult($result);
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_EDIT_AD'	=> true,
			'EDIT_ID'	=> $ad_id,
			'U_BACK'	=> $this->u_action,

			'AD_NAME'		=> $data['ad_name'],
			'AD_NOTE'		=> $data['ad_note'],
			'AD_CODE'		=> $data['ad_code'],
			'AD_ENABLED'	=> $data['ad_enabled'],
		));
	}

	/**
	* Enable/disable ad
	*
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
				'text'	=> $enable ? $this->user->lang('ENABLED') : $this->user->lang('DISABLED'),
				'title'	=> $this->user->lang('AD_ENABLE_TITLE', (int) $enable),
			));
		}

		// Otherwise, show traditional infobox
		if ($success)
		{
			trigger_error($this->user->lang($enable ? 'ACP_AD_ENABLE_SUCCESS' : 'ACP_AD_DISABLE_SUCCESS') . adm_back_link($this->u_action));
		}
		else
		{
			trigger_error($this->user->lang($enable ? 'ACP_AD_ENABLE_ERRORED' : 'ACP_AD_DISABLE_ERRORED') . adm_back_link($this->u_action), E_USER_WARNING);
		}
	}

	/**
	* Process 'delete' action
	*
	* @return void
	*/
	public function action_delete()
	{
		$sql = 'DELETE FROM ' . $this->ads_table . '
			WHERE ad_id = ' . (int) $this->request->variable('id', 0);
		$this->db->sql_query($sql);

		if ($this->db->sql_affectedrows())
		{
			trigger_error($this->user->lang('ACP_AD_DELETE_SUCCESS') . adm_back_link($this->u_action));
		}
		else
		{
			trigger_error($this->user->lang('ACP_AD_DELETE_ERRORED') . adm_back_link($this->u_action), E_USER_WARNING);
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
			$ad_enabled = (bool) $row['ad_enabled'];

			$this->template->assign_block_vars('ads', array( // TODO: convert back to original notation (3.1 does not support this)
				'NAME'		=> $row['ad_name'],
				'S_ENABLED'	=> $ad_enabled,
				'ENABLED'	=> (int) $ad_enabled,
				'U_ENABLE'	=> $this->u_action . '&amp;action=' . ($ad_enabled ? 'disable' : 'enable') . '&amp;id=' . $row['ad_id'], // TODO: ACP method
				'U_PREVIEW'	=> '', // TODO: frontend logic
				'U_EDIT'	=> $this->u_action . '&amp;action=edit&amp;id=' . $row['ad_id'], // TODO: ACP method
				'U_DELETE'	=> $this->u_action . '&amp;action=delete&amp;id=' . $row['ad_id'], // TODO: ACP method
			));
		}
		$this->db->sql_freeresult($result);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'U_ACTION_ADD'	=> $this->u_action . '&amp;action=add',
		));
	}
}
