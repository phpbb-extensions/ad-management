<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\analyser\test;

/**
 * Interface for ad code analysis tests
 */
interface test_interface
{
	/**
	 * Test ad code for potential problems.
	 *
	 * @param	string	$ad_code	Advertisement code
	 * @return	mixed	List of notices and warnings or false when there are none.
	 */
	public function run($ad_code);
}
