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

/**
* Base class for template location types
*/
abstract class base implements \phpbb\admanagement\location\type\type_interface
{
	/**
	* {@inheritDoc}
	*/
	public function will_display()
	{
		return true;
	}
}
