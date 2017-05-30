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

class below_header extends base
{
	/**
	* User object
	* @var \phpbb\user
	*/
	protected $user;

	/**
	* Construct an above_header template location object
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
		return 'below_header';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_name()
	{
		return $this->user->lang('AD_BELOW_HEADER');
	}

	/**
	* {@inheritDoc}
	*/
	public function get_desc()
	{
		return $this->user->lang('AD_BELOW_HEADER_DESC');
	}
}
