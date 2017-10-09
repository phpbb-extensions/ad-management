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
	'AD_NAME'		=> 'Name',
	'AD_END_DATE'	=> 'End Date',
	'AD_VIEWS'		=> 'Views',
	'AD_CLICKS'		=> 'Clicks',
	'AD_STATUS'		=> 'Status',
	'EXPIRED'		=> 'Expired',
	'ACTIVE_ADS'	=> 'Active ads',
	'EXPIRED_ADS'	=> 'Expired ads',
	'NO_ADS'		=> '<strong>You do not have any advertisements displayed on this board.</strong>',
));
