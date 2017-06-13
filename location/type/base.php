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

/**
* Base class for template location types
*/
abstract class base implements \phpbb\ads\location\type\type_interface
{
	/**
	* User object
	* @var \phpbb\user
	*/
	protected $user;

	/**
	* Construct an after_profile template location object
	*
	* @param	\phpbb\user	$user	User object
	*/
	public function __construct(\phpbb\user $user)
	{
		$this->user = $user;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_name()
	{
		return $this->user->lang('AD_' . strtoupper($this->get_id()));
	}

	/**
	* {@inheritDoc}
	*/
	public function get_desc()
	{
		return $this->user->lang('AD_' . strtoupper($this->get_id()) . '_DESC');
	}

	/**
	* {@inheritDoc}
	*/
	public function will_display()
	{
		return true;
	}
}
