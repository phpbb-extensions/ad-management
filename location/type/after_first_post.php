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

class after_first_post extends base
{
	/**
	* {@inheritDoc}
	*/
	public function get_id()
	{
		return 'after_first_post';
	}

	/**
	* {@inheritDoc}
	*/
	public function will_display()
	{
		return strpos($this->user->page['page_name'], 'viewtopic') !== false;
	}
}