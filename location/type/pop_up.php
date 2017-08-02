<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\location\type;

class pop_up extends base
{
	/** @var \phpbb\request\request */
	protected $request;
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \phpbb\template\template */
	protected $template;

	/**
	 * pop_up location constructor.
	 *
	 * @param \phpbb\user              $user     User object
	 * @param \phpbb\language\language $language Language object
	 * @param \phpbb\request\request   $request  Request object
	 * @param \phpbb\config\config     $config   Config object
	 * @param \phpbb\template\template $template Template object
	 */
	public function __construct(\phpbb\user $user, \phpbb\language\language $language, \phpbb\request\request $request, \phpbb\config\config $config, \phpbb\template\template $template)
	{
		parent::__construct($user, $language);

		$this->request = $request;
		$this->config = $config;
		$this->template = $template;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_id()
	{
		return 'pop_up';
	}

	/**
	 * {@inheritDoc}
	 */
	public function will_display()
	{
		if ($this->request->is_set($this->config['cookie_name'] . '_pop_up', \phpbb\request\request_interface::COOKIE))
		{
			return false;
		}

		$this->template->assign_vars(array(
			'POP_UP_COOKIE_NAME'	=> $this->config['cookie_name'] . '_pop_up',
			'POP_UP_COOKIE_EXPIRES'	=> gmdate('D, d M Y H:i:s T', strtotime('+1 day')),
			'POP_UP_COOKIE_PATH'	=> $this->config['cookie_path'],
		));
		return true;
	}
}
