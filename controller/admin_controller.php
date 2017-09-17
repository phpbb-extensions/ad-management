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

use phpbb\ads\ext;

/**
* Admin controller
*/
class admin_controller
{
	/** @var array Form data */
	protected $data = array();

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\ads\ad\manager */
	protected $manager;

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\group\helper */
	protected $group_helper;

	/** @var \phpbb\ads\controller\admin_input */
	protected $input;

	/** @var \phpbb\ads\controller\admin_helper */
	protected $helper;

	/** @var \phpbb\ads\analyser\manager */
	protected $analyser;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor
	 *
	 * @param \phpbb\template\template           $template     Template object
	 * @param \phpbb\language\language           $language     Language object
	 * @param \phpbb\request\request             $request      Request object
	 * @param \phpbb\ads\ad\manager              $manager      Advertisement manager object
	 * @param \phpbb\config\db_text              $config_text  Config text object
	 * @param \phpbb\config\config               $config       Config object
	 * @param \phpbb\group\helper                $group_helper Group helper object
	 * @param \phpbb\ads\controller\admin_input  $input        Admin input object
	 * @param \phpbb\ads\controller\admin_helper $helper       Admin helper object
	 * @param \phpbb\ads\analyser\manager        $analyser     Ad code analyser object
	 */
	public function __construct(\phpbb\template\template $template, \phpbb\language\language $language, \phpbb\request\request $request, \phpbb\ads\ad\manager $manager, \phpbb\config\db_text $config_text, \phpbb\config\config $config, \phpbb\group\helper $group_helper, \phpbb\ads\controller\admin_input $input, \phpbb\ads\controller\admin_helper $helper, \phpbb\ads\analyser\manager $analyser)
	{
		$this->template = $template;
		$this->language = $language;
		$this->request = $request;
		$this->manager = $manager;
		$this->config_text = $config_text;
		$this->config = $config;
		$this->group_helper = $group_helper;
		$this->input = $input;
		$this->helper = $helper;
		$this->analyser = $analyser;

		$this->language->add_lang('posting'); // Used by banner_upload() file errors
		$this->language->add_lang('acp', 'phpbb/ads');

		$this->template->assign_var('S_PHPBB_ADS', true);
	}

	/**
	 * Set page url
	 *
	 * @param	string	$u_action	Custom form action
	 * @return	void
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

	/**
	 * Get ACP page title for Ads module
	 *
	 * @return	string	Language string for Ads ACP module
	 */
	public function get_page_title()
	{
		return $this->language->lang('ACP_PHPBB_ADS_TITLE');
	}

	/**
	 * Process user request for settings mode
	 *
	 * @return	void
	 */
	public function mode_settings()
	{
		if ($this->request->is_set_post('submit'))
		{
			// Validate form key
			if (check_form_key('phpbb_ads'))
			{
				$this->config->set('phpbb_ads_adblocker_message', $this->request->variable('adblocker_message', 0));
				$this->config->set('phpbb_ads_enable_views', $this->request->variable('enable_views', 0));
				$this->config->set('phpbb_ads_enable_clicks', $this->request->variable('enable_clicks', 0));
				$this->config_text->set('phpbb_ads_hide_groups', json_encode($this->request->variable('hide_groups', array(0))));

				$this->success('ACP_AD_SETTINGS_SAVED');
			}

			$this->error('FORM_INVALID');
		}

		$hide_groups = json_decode($this->config_text->get('phpbb_ads_hide_groups'), true);
		$groups = $this->manager->load_groups();
		foreach ($groups as $group)
		{
			$this->template->assign_block_vars('groups', array(
				'ID'         => $group['group_id'],
				'NAME'       => $this->group_helper->get_name($group['group_name']),
				'S_SELECTED' => in_array($group['group_id'], $hide_groups),
			));
		}

		$this->template->assign_vars(array(
			'U_ACTION'          => $this->u_action,
			'ADBLOCKER_MESSAGE' => $this->config['phpbb_ads_adblocker_message'],
			'ENABLE_VIEWS'      => $this->config['phpbb_ads_enable_views'],
			'ENABLE_CLICKS'     => $this->config['phpbb_ads_enable_clicks'],
		));
	}

	/**
	 * Process user request for manage mode
	 *
	 * @return	void
	 */
	public function mode_manage()
	{
		// Trigger specific action
		$action = $this->request->variable('action', '');
		if (in_array($action, array('add', 'edit', 'enable', 'disable', 'delete')))
		{
			$this->{'action_' . $action}();
		}
		else
		{
			// Otherwise default to this
			$this->list_ads();
		}
	}

	/**
	 * Add an advertisement
	 *
	 * @return	void
	 */
	protected function action_add()
	{
		$action = $this->get_submitted_action();
		if ($action !== false)
		{
			$this->data = $this->input->get_form_data();
			$this->{$action}();
			$this->helper->assign_data($this->data, $this->input->get_errors());
		}
		else
		{
			$this->helper->assign_locations();
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ADD_AD'				=> true,
			'U_BACK'				=> $this->u_action,
			'U_ACTION'				=> "{$this->u_action}&amp;action=add",
			'PICKER_DATE_FORMAT'	=> ext::DATE_FORMAT,
			'U_FIND_USERNAME'		=> $this->helper->get_find_username_link(),
		));
	}

	/**
	 * Edit an advertisement
	 *
	 * @return	void
	 */
	protected function action_edit()
	{
		$ad_id = $this->request->variable('id', 0);
		$action = $this->get_submitted_action();
		if ($action !== false)
		{
			$this->data = $this->input->get_form_data();
			$this->{$action}();
		}
		else
		{
			$this->data = $this->manager->get_ad($ad_id);
			if (empty($this->data))
			{
				$this->error('ACP_AD_DOES_NOT_EXIST');
			}
			// Load ad template locations
			$this->data['ad_locations'] = $this->manager->get_ad_locations($ad_id);
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_EDIT_AD'				=> true,
			'EDIT_ID'				=> $ad_id,
			'U_BACK'				=> $this->u_action,
			'U_ACTION'				=> "{$this->u_action}&amp;action=edit&amp;id=$ad_id",
			'PICKER_DATE_FORMAT'	=> ext::DATE_FORMAT,
			'U_FIND_USERNAME'		=> $this->helper->get_find_username_link(),
		));
		$this->helper->assign_data($this->data, $this->input->get_errors());
	}

	/**
	 * Enable an advertisement
	 *
	 * @return	void
	 */
	protected function action_enable()
	{
		$this->ad_enable(true);
	}

	/**
	 * Disable an advertisement
	 *
	 * @return	void
	 */
	protected function action_disable()
	{
		$this->ad_enable(false);
	}

	/**
	 * Delete an advertisement
	 *
	 * @return	void
	 */
	protected function action_delete()
	{
		$ad_id = $this->request->variable('id', 0);
		if ($ad_id)
		{
			if (confirm_box(true))
			{
				// Get ad data so that we can log ad name
				$ad_data = $this->manager->get_ad($ad_id);

				// Delete ad and it's template locations
				$this->manager->delete_ad_locations($ad_id);
				$success = $this->manager->delete_ad($ad_id);

				// Only notify user on error or if not ajax
				if (!$success)
				{
					$this->error('ACP_AD_DELETE_ERRORED');
				}
				else
				{
					$this->helper->log('DELETE', $ad_data['ad_name']);

					if (!$this->request->is_ajax())
					{
						$this->success('ACP_AD_DELETE_SUCCESS');
					}
				}
			}
			else
			{
				confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields(array(
					'id'     => $ad_id,
					'i'      => $this->request->variable('i', ''),
					'mode'   => $this->request->variable('mode', ''),
					'action' => 'delete',
				)));
			}
		}
	}

	/**
	 * Display the list of all ads
	 *
	 * @return	void
	 */
	protected function list_ads()
	{
		foreach ($this->manager->get_all_ads() as $row)
		{
			$ad_enabled = (int) $row['ad_enabled'];
			$ad_expired = $this->helper->is_expired($row);

			if ($ad_expired && $ad_enabled)
			{
				$ad_enabled = 0;
				$this->manager->update_ad($row['ad_id'], array('ad_enabled' => 0));
			}

			$this->template->assign_block_vars($ad_expired ? 'expired' : 'ads', array(
				'NAME'         => $row['ad_name'],
				'PRIORITY'     => $row['ad_priority'],
				'END_DATE'     => $row['ad_end_date'],
				'VIEWS'        => $row['ad_views'],
				'CLICKS'       => $row['ad_clicks'],
				'VIEWS_LIMIT'  => $row['ad_views_limit'],
				'CLICKS_LIMIT' => $row['ad_clicks_limit'],
				'S_EXPIRED'    => $ad_expired,
				'S_ENABLED'    => $ad_enabled,
				'U_ENABLE'     => $this->u_action . '&amp;action=' . ($ad_enabled ? 'disable' : 'enable') . '&amp;id=' . $row['ad_id'],
				'U_EDIT'       => $this->u_action . '&amp;action=edit&amp;id=' . $row['ad_id'],
				'U_DELETE'     => $this->u_action . '&amp;action=delete&amp;id=' . $row['ad_id'],
			));
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'U_ACTION_ADD'     => $this->u_action . '&amp;action=add',
			'S_VIEWS_ENABLED'  => $this->config['phpbb_ads_enable_views'],
			'S_CLICKS_ENABLED' => $this->config['phpbb_ads_enable_clicks'],
		));
	}

	/**
	 * Get what action user wants to do with the form.
	 * Possible options are:
	 *  - preview ad code
	 *  - upload banner to display in an ad code
	 *  - analyse ad code
	 *  - submit form (either add or edit an ad)
	 *
	 * @return	string|false	Action name or false when no action was submitted
	 */
	protected function get_submitted_action()
	{
		$actions = array('preview', 'upload_banner', 'analyse_ad_code', 'submit_add', 'submit_edit');
		foreach ($actions as $action)
		{
			if ($this->request->is_set_post($action))
			{
				return $action;
			}
		}

		return false;
	}

	/**
	 * Enable/disable an advertisement
	 *
	 * @param	bool	$enable	Enable or disable the advertisement?
	 * @return	void
	 */
	protected function ad_enable($enable)
	{
		$ad_id = $this->request->variable('id', 0);

		$success = $this->manager->update_ad($ad_id, array(
			'ad_enabled' => (int) $enable,
		));

		// If AJAX was used, show user a result message
		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send(array(
				'text'  => $this->language->lang($enable ? 'ENABLED' : 'DISABLED'),
				'title' => $this->language->lang('AD_ENABLE_TITLE', (int) $enable),
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
	 * Submit action "preview".
	 * Prepare advertisement preview.
	 *
	 * @return	void
	 */
	protected function preview()
	{
		$this->template->assign_var('PREVIEW', htmlspecialchars_decode($this->data['ad_code']));
	}

	/**
	 * Submit action "upload_banner".
	 * Upload banner and append it to the ad code.
	 *
	 * @return	void
	 */
	protected function upload_banner()
	{
		$this->data['ad_code'] = $this->input->banner_upload($this->data['ad_code']);
	}

	/**
	 * Submit action "analyse_ad_code".
	 * Upload banner and append it to the ad code.
	 *
	 * @return	void
	 */
	protected function analyse_ad_code()
	{
		$this->analyser->run($this->data['ad_code']);
	}

	/**
	 * Submit action "submit_add".
	 * Add new ad.
	 *
	 * @return	void
	 */
	protected function submit_add()
	{
		if (!$this->input->has_errors())
		{
			$ad_id = $this->manager->insert_ad($this->data);
			$this->manager->insert_ad_locations($ad_id, $this->data['ad_locations']);

			$this->helper->log('ADD', $this->data['ad_name']);

			$this->success('ACP_AD_ADD_SUCCESS');
		}
	}

	/**
	 * Submit action "submit_edit".
	 * Edit ad.
	 *
	 * @return	void
	 */
	protected function submit_edit()
	{
		$ad_id = $this->request->variable('id', 0);
		if ($ad_id && !$this->input->has_errors())
		{
			$success = $this->manager->update_ad($ad_id, $this->data);
			if ($success)
			{
				// Only insert new ad locations to DB when ad exists
				$this->manager->delete_ad_locations($ad_id);
				$this->manager->insert_ad_locations($ad_id, $this->data['ad_locations']);

				$this->helper->log('EDIT', $this->data['ad_name']);

				$this->success('ACP_AD_EDIT_SUCCESS');
			}

			$this->error('ACP_AD_DOES_NOT_EXIST');
		}
	}

	/**
	 * Print success message.
	 *
	 * @param	string	$msg	Message lang key
	 */
	protected function success($msg)
	{
		trigger_error($this->language->lang($msg) . adm_back_link($this->u_action));
	}

	/**
	 * Print error message.
	 *
	 * @param	string	$msg	Message lang key
	 */
	protected function error($msg)
	{
		trigger_error($this->language->lang($msg) . adm_back_link($this->u_action), E_USER_WARNING);
	}
}
