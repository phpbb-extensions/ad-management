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
	'ACP_ADMANAGEMENT_TITLE'	=> 'Advertisement Management',
	'ACP_MANAGE_ADS_TITLE'		=> 'Manage advertisements',
	'ACP_ADS_SETTINGS_TITLE'	=> 'Settings',

	'ACP_ADMANAGEMENT_ADD_LOG'		=> '<strong>Advertisement added</strong><br />» %s',
	'ACP_ADMANAGEMENT_EDIT_LOG'		=> '<strong>Advertisement edited</strong><br />» %s',
	'ACP_ADMANAGEMENT_DELETE_LOG'	=> '<strong>Advertisement deleted</strong><br />» %s',
));
