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

class above_header extends base
{
	/**
	* {@inheritDoc}
	*/
	public function get_id()
	{
		return 'above_header';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_name()
	{
		return $this->user->lang('AD_ABOVE_HEADER');
	}

	/**
	* {@inheritDoc}
	*/
	public function get_desc()
	{
		return $this->user->lang('AD_ABOVE_HEADER_DESC');
	}
}
