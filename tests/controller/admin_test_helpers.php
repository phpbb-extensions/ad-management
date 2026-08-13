<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\controller;

class admin_test_state
{
	public static bool $valid_form = true;
}

/**
 * Mock check_form_key().
 *
 * @return bool
 */
function check_form_key(): bool
{
	return admin_test_state::$valid_form;
}

/**
 * Mock add_form_key().
 */
function add_form_key()
{
}
