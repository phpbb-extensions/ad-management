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
	'AD_NAME'			=> 'Name',
	'AD_ENABLED'		=> 'Enabled',
	'CLICK_TO_DISABLE'	=> 'Click to disable',
	'CLICK_TO_ENABLE'	=> 'Click to enable',
	'ACP_ADS_EMPTY'		=> 'No advertisement, yet. Add one with the button below.',
	'ACP_ADS_ADD'		=> 'Add new advertisement',
));
