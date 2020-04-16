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
	'AD_NAME'		=> 'Название',
	'AD_START_DATE'	=> 'Дата начала',
	'AD_END_DATE'	=> 'Дата окончания',
	'AD_VIEWS'		=> 'Просмотры',
	'AD_CLICKS'		=> 'Клики',
	'AD_STATUS'		=> 'Статус',
	'EXPIRED'		=> 'Завершено',
	'ACTIVE_ADS'	=> 'Активные объявления',
	'EXPIRED_ADS'	=> 'Завершенные объявления',
	'NO_ADS'		=> '<strong>У вас нет рекламы на этом форуме.</strong>',
));
