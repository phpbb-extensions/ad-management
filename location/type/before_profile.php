<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\admanagement\location\type;

class before_profile extends base
{
	/**
	* User object
	* @var \phpbb\user
	*/
	protected $user;

	/**
	* Construct an before_profile template location object
	*
	* @param	\phpbb\user	$config	User object
	*/
	public function __construct(\phpbb\user $user)
	{
		$this->user = $user;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_id()
	{
		return 'before_profile';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_name()
	{
		return $this->user->lang('AD_BEFORE_PROFILE');
	}

	/**
	* {@inheritDoc}
	*/
	public function get_desc()
	{
		return $this->user->lang('AD_BEFORE_PROFILE_DESC');
	}

	/**
	* {@inheritDoc}
	*/
	public function will_display()
	{
		return strpos($this->user->page['page_name'], 'memberlist') !== false && strpos($this->user->page['query_string'], 'viewprofile') !== false;
	}
}
