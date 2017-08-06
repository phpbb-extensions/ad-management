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
	'ADBLOCKER_TITLE'	=> 'Ad blocker detected',
	'ADBLOCKER_MESSAGE'	=> 'Our website is made possible by displaying online advertisements to our visitors. Please consider supporting us by disabling your ad blocker on our website.',

	'ADVERTISEMENT'		=> 'Advertisement',
	'HIDE_AD'			=> 'Hide advertisement',
));
