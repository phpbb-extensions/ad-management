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
* Interface for template location types
*/
interface type_interface
{
	/**
	 * Returns the unique ID of the location.
	 *
	 * @return string	ID of location.
	 */
	public function get_id();

	/**
	 * Returns the name of the location.
	 *
	 * @return string	Name of location.
	 */
	public function get_name();

	/**
	 * Returns the description of the location.
	 *
	 * @return string	Description of location.
	 */
	public function get_desc();

	/**
	 * Returns whether or not this location type will be displayed on a current page.
	 *
	 * Generally, you can always return true, but if you can narrow down the usage
	 * without adding extra load to server, this will further enhance the extension's
	 * performance.
	 *
	 * @return bool	True when location type will be displayed on a current page and false if not.
	 */
	public function will_display();
}
