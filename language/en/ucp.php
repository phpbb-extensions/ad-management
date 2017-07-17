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
	'AD_NAME'	=> 'Name',
	'AD_VIEWS'	=> 'Views',
	'AD_CLICKS'	=> 'Clicks',
	'NO_ADS'	=> 'No advertisements to display. When you become an owner of one, it will immediately display its statistics here.',

	'AD_VIEWS_DISABLED'		=> 'Advertisement views are not counted on this board, so they are not display in the table.',
	'AD_CLICKS_DISABLED'	=> 'Advertisement clicks are not counted on this board, so they are not display in the table.',
));
