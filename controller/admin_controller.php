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

			$this->template->assign_block_vars('ads', [
				'NAME'		=> $row['ad_name'],
				'S_ENABLED'	=> $ad_enabled,
				'U_ENABLE'	=> $this->u_action . '&amp;action=' . ($ad_enabled ? 'disable' : 'enable') . '&amp;id=' . $row['ad_id'], // TODO: ACP method
				'U_PREVIEW'	=> '', // TODO: frontend logic
				'U_EDIT'	=> $this->u_action . '&amp;action=edit&amp;id=' . $row['ad_id'], // TODO: ACP method
				'U_DELETE'	=> $this->u_action . '&amp;action=delete&amp;id=' . $row['ad_id'], // TODO: ACP method
			]);
		}
		$this->db->sql_freeresult($result);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'U_ACTION_ADD'	=> $this->u_action . '&amp;action=add',
		));
	}
}
