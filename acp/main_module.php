<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\admanagement\acp;

/**
 * Advertisement management ACP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $config, $request, $template, $user;

		$user->add_lang_ext('phpbb/admanagement', 'common');
		$this->tpl_name = 'acp_admanagement';
		$this->page_title = $user->lang('ACP_ADMANAGEMENT_TITLE');
		add_form_key('phpbb/admanagement');

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('phpbb/admanagement'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			$config->set('acme_demo_goodbye', $request->variable('acme_demo_goodbye', 0));

			trigger_error($user->lang('ACP_DEMO_SETTING_SAVED') . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'U_ACTION'				=> $this->u_action,
			'ACME_DEMO_GOODBYE'		=> $config['acme_demo_goodbye'],
		));
	}
}
