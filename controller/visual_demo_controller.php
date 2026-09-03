<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\controller;

/**
 * Visual demo controller
 */
class visual_demo_controller
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth       $auth
	 * @param \phpbb\request\request $request
	 * @param \phpbb\user            $user
	 * @param string                 $root_path
	 * @param string                 $php_ext
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\request\request $request, \phpbb\user $user, $root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->request = $request;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Visual demo handler
	 *
	 * When called by an admin, add or remove the visual demo cookie
	 * and direct them to an appropriate forum page to view.
	 *
	 * @param string $action enable|disable
	 * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
	 * @throws \phpbb\exception\http_exception http exception
	 */
	public function handle($action)
	{
		// Protect against unauthorised access
		if (!$this->auth->acl_get('a_'))
		{
			throw new \phpbb\exception\http_exception(403, 'NO_AUTH_OPERATION');
		}

		if (!check_link_hash($this->request->variable('hash', ''), 'phpbb_ads_visual_demo_' . $action))
		{
			throw new \phpbb\exception\http_exception(403, 'FORM_INVALID');
		}

		if ($action === 'disable')
		{
			// Destroy our cookie and redirect user to previous page viewed.
			$this->user->set_cookie('phpbb_ads_visual_demo', '', 1);
			$redirect = $this->request->variable('redirect', $this->user->data['session_page']);
		}
		else
		{
			// Create our cookie and send user to the index page.
			$this->user->set_cookie('phpbb_ads_visual_demo', time(), 0);
			$redirect = "{$this->root_path}index.$this->php_ext";
		}

		// Send a JSON response if an AJAX request was used
		if ($this->request->is_ajax())
		{
			return new \Symfony\Component\HttpFoundation\JsonResponse(array(
				'success' => true,
			));
		}

		// Redirect user to a page
		return new \Symfony\Component\HttpFoundation\RedirectResponse(reapply_sid($redirect));
	}
}
