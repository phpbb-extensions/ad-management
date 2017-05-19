<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'DEMO_PAGE'			=> 'Demo',
	'DEMO_HELLO'		=> 'Hello %s!',
	'DEMO_GOODBYE'		=> 'Goodbye %s!',

	'ACP_DEMO'					=> 'Settings',
	'ACP_DEMO_GOODBYE'			=> 'Should say goodbye?',
	'ACP_DEMO_SETTING_SAVED'	=> 'Settings have been saved successfully!',

	'ACME_DEMO_NOTIFICATION'	=> 'Acme demo notification',

	'VIEWING_ACME_DEMO'			=> 'Viewing Acme Demo',
));
