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

class before_posts extends base
{
	/**
	 * {@inheritDoc}
	 */
	public function get_id()
	{
		return 'before_posts';
	}

	/**
	 * {@inheritDoc}
	 */
	public function will_display()
	{
		return strpos($this->user->page['page_name'], 'viewtopic') !== false;
	}
}
