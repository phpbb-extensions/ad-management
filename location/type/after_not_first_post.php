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

class after_not_first_post extends base
{
	/**
	* {@inheritDoc}
	*/
	public function get_id()
	{
		return 'after_not_first_post';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_name()
	{
		return $this->user->lang('AD_AFTER_NOT_FIRST_POST');
	}

	/**
	* {@inheritDoc}
	*/
	public function get_desc()
	{
		return $this->user->lang('AD_AFTER_NOT_FIRST_POST_DESC');
	}

	/**
	* {@inheritDoc}
	*/
	public function will_display()
	{
		return strpos($this->user->page['page_name'], 'viewtopic') !== false;
	}
}
