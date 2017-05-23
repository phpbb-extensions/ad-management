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
	'AD_ENABLE_TITLE' => array( // Plural rule doesn't apply here! Just translate the values.
		0 => 'Click to enable',
		1 => 'Click to disable',
	),
	'ACP_ADS_EMPTY'		=> 'No advertisement, yet. Add one with the button below.',
	'ACP_ADS_ADD'		=> 'Add new advertisement',
));
