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

use phpbb\ads\controller\admin_input as input;

/**
* Admin controller
*/
class admin_controller
{
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

	/** @var string root_path */
	protected $root_path;

	/** @var string php_ext */
	protected $php_ext;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor
	 *
	 * @param \phpbb\template\template				$template		Template object
	 * @param \phpbb\language\language				$language		Language object
	 * @param \phpbb\request\request				$request		Request object
	 * @param \phpbb\ads\ad\manager					$manager		Advertisement manager object
	 * @param \phpbb\config\db_text					$config_text 	Config text object
	 * @param \phpbb\config\config					$config			Config object
	 * @param \phpbb\group\helper					$group_helper	Group helper object
	 * @param \phpbb\ads\controller\admin_input 	$input			Admin input object
	 * @param \phpbb\ads\controller\admin_helper	$helper			Admin helper object
	 * @param \phpbb\ads\analyser\manager			$analyser		Ad code analyser object
	 * @param string								$root_path		phpBB root path
	 * @param string								$php_ext		PHP extension
	 */
	public function __construct(\phpbb\template\template $template, \phpbb\language\language $language, \phpbb\request\request $request, \phpbb\ads\ad\manager $manager, \phpbb\config\db_text $config_text, \phpbb\config\config $config, \phpbb\group\helper $group_helper, \phpbb\ads\controller\admin_input $input, \phpbb\ads\controller\admin_helper $helper, \phpbb\ads\analyser\manager $analyser, $root_path, $php_ext)
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
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Process user request for manage mode
	 *
	 * @return void
	 */
	public function mode_manage()
	{
		$this->setup();

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
	 * Process user request for settings mode
	 *
	 * @return void
	 */
	public function mode_settings()
	{
		$this->setup();

		add_form_key('phpbb/ads/settings');
		if ($this->request->is_set_post('submit'))
		{
			// Validate form key
			if (check_form_key('phpbb/ads/settings'))
			{
				$this->config->set('phpbb_ads_adblocker_message', $this->request->variable('adblocker_message', 0));
				$this->config->set('phpbb_ads_enable_views', $this->request->variable('enable_views', 0));
				$this->config->set('phpbb_ads_enable_clicks', $this->request->variable('enable_clicks', 0));
				$this->config_text->set('phpbb_ads_hide_groups', json_encode($this->request->variable('hide_groups', array(0))));

				$this->success('ACP_AD_SETTINGS_SAVED');
			}

			$this->helper->assign_errors(array($this->language->lang('FORM_INVALID')));
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
	 * @return string    Language string for Ads ACP module
	 */
	public function get_page_title()
	{
		return $this->language->lang('ACP_PHPBB_ADS_TITLE');
	}

	/**
	 * Add an advertisement
	 *
	 * @return void
	 */
	public function action_add()
	{
		$preview = $this->request->is_set_post('preview');
		$submit = $this->request->is_set_post('submit');
		$upload_banner = $this->request->is_set_post('upload_banner');
		$analyse_ad_code = $this->request->is_set_post('analyse_ad_code');

		add_form_key('phpbb/ads/add');
		if ($preview || $submit || $upload_banner || $analyse_ad_code)
		{
			$data = $this->input->get_form_data('phpbb/ads/add');

			if ($preview)
			{
				$this->ad_preview($data['ad_code']);
			}
			else if ($upload_banner)
			{
				$data['ad_code'] = $this->input->banner_upload($data['ad_code']);
			}
			else if ($analyse_ad_code)
			{
				$this->analyser->run($data['ad_code']);
			}
			else if (!$this->input->has_errors())
			{
				$ad_id = $this->manager->insert_ad($data);
				$this->manager->insert_ad_locations($ad_id, $data['ad_locations']);

				$this->helper->log('ADD', $data['ad_name']);

				$this->success('ACP_AD_ADD_SUCCESS');
			}

			$this->helper->assign_locations($data['ad_locations']);
			$this->helper->assign_form_data($data);
			$this->helper->assign_errors($this->input->get_errors());
		}
		else
		{
			$this->helper->assign_locations();
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ADD_AD'           => true,
			'U_BACK'             => $this->u_action,
			'U_ACTION'           => "{$this->u_action}&amp;action=add",
			'PICKER_DATE_FORMAT' => input::DATE_FORMAT,
			'U_FIND_USERNAME'    => $this->helper->get_find_username_link(),
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
		$preview = $this->request->is_set_post('preview');
		$submit = $this->request->is_set_post('submit');
		$upload_banner = $this->request->is_set_post('upload_banner');
		$analyse_ad_code = $this->request->is_set_post('analyse_ad_code');

		add_form_key('phpbb/ads/edit/' . $ad_id);
		if ($preview || $submit || $upload_banner || $analyse_ad_code)
		{
			$data = $this->input->get_form_data('phpbb/ads/edit/' . $ad_id);

			if ($preview)
			{
				$this->ad_preview($data['ad_code']);
			}
			else if ($upload_banner)
			{
				$data['ad_code'] = $this->input->banner_upload($data['ad_code']);
			}
			else if ($analyse_ad_code)
			{
				$this->analyser->run($data['ad_code']);
			}
			else if (!$this->input->has_errors())
			{
				$success = $this->manager->update_ad($ad_id, $data);

				if ($success)
				{
					// Only insert new ad locations to DB when ad exists
					$this->manager->delete_ad_locations($ad_id);
					$this->manager->insert_ad_locations($ad_id, $data['ad_locations']);

					$this->helper->log('EDIT', $data['ad_name']);

					$this->success('ACP_AD_EDIT_SUCCESS');
				}

				$this->error('ACP_AD_DOES_NOT_EXIST');
			}
		}
		else
		{
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
			'S_EDIT_AD'          => true,
			'EDIT_ID'            => $ad_id,
			'U_BACK'             => $this->u_action,
			'U_ACTION'           => "{$this->u_action}&amp;action=edit&amp;id=" . $ad_id,
			'PICKER_DATE_FORMAT' => input::DATE_FORMAT,
			'U_FIND_USERNAME'    => $this->helper->get_find_username_link(),
		));
		$this->helper->assign_locations($data['ad_locations']);
		$this->helper->assign_form_data($data);
		$this->helper->assign_errors($this->input->get_errors());
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
					'action' => 'delete'
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
		$no_ads = true;
		foreach ($this->manager->get_all_ads() as $row)
		{
			$no_ads = false;
			$ad_enabled = (int) $row['ad_enabled'];
			$ad_end_date = (int) $row['ad_end_date'];
			$ad_expired = $ad_end_date > 0 && $ad_end_date < time();
			if ($ad_expired && $ad_enabled)
			{
				$ad_enabled = 0;
				$this->manager->update_ad($row['ad_id'], array('ad_enabled' => 0));
			}
			$ad_force_disabled = $ad_expired || ($row['ad_views_limit'] && $row['ad_views'] >= $row['ad_views_limit']) || ($row['ad_clicks_limit'] && $row['ad_clicks'] >= $row['ad_clicks_limit']);

			$this->template->assign_block_vars($ad_force_disabled ? 'force_disabled' : 'ads', array(
				'NAME'               => $row['ad_name'],
				'PRIORITY'			 => $row['ad_priority'],
				'END_DATE'           => $this->helper->prepare_end_date($ad_end_date),
				'VIEWS'              => $row['ad_views'],
				'CLICKS'             => $row['ad_clicks'],
				'VIEWS_LIMIT'        => $row['ad_views_limit'],
				'CLICKS_LIMIT'       => $row['ad_clicks_limit'],
				'S_END_DATE_EXPIRED' => $ad_expired,
				'S_ENABLED'          => $ad_enabled,
				'U_ENABLE'           => $this->u_action . '&amp;action=' . ($ad_enabled ? 'disable' : 'enable') . '&amp;id=' . $row['ad_id'],
				'U_EDIT'             => $this->u_action . '&amp;action=edit&amp;id=' . $row['ad_id'],
				'U_DELETE'           => $this->u_action . '&amp;action=delete&amp;id=' . $row['ad_id'],
			));
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_NO_ADS'		   => $no_ads,
			'U_ACTION_ADD'     => $this->u_action . '&amp;action=add',
			'S_VIEWS_ENABLED'  => $this->config['phpbb_ads_enable_views'],
			'S_CLICKS_ENABLED' => $this->config['phpbb_ads_enable_clicks'],
		));
	}

	/**
	 * Perform general tasks
	 *
	 * @return void
	 */
	protected function setup()
	{
		if (!function_exists('user_get_id_name'))
		{
			include $this->root_path . 'includes/functions_user.' . $this->php_ext;
		}

		$this->language->add_lang('posting'); // Used by banner_upload() file errors
		$this->language->add_lang('acp', 'phpbb/ads');

		$this->template->assign_var('S_PHPBB_ADS', true);
	}

	/**
	 * Enable/disable an advertisement
	 *
	 * @param    bool $enable Enable or disable the advertisement?
	 * @return void
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
	 * Prepare advertisement preview
	 *
	 * @param    string $code Ad code to preview
	 * @return    void
	 */
	protected function ad_preview($code)
	{
		$this->template->assign_var('PREVIEW', htmlspecialchars_decode($code));
	}

	/**
	 * Print success message.
	 *
	 * It takes arguments in the form of a language key, followed by language substitution values.
	 */
	protected function success()
	{
		trigger_error(call_user_func_array(array($this->language, 'lang'), func_get_args()) . adm_back_link($this->u_action));
	}

	/**
	 * Print error message.
	 *
	 * It takes arguments in the form of a language key, followed by language substitution values.
	 */
	protected function error()
	{
		trigger_error(call_user_func_array(array($this->language, 'lang'), func_get_args()) . adm_back_link($this->u_action), E_USER_WARNING);
	}
}
