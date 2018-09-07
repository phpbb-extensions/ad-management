<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\location\type;

class scripts extends base
{
	/**
	 * {@inheritDoc}
	 */
	public function get_id()
	{
		return 'scripts';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_category()
	{
		return self::CAT_SPECIAL;
	}
}
