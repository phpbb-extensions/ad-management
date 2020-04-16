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
	'ACP_PHPBB_ADS_TITLE'	=> 'Управление рекламой',
	'ACP_MANAGE_ADS_TITLE'		=> 'Управление рекламой',
	'ACP_ADS_SETTINGS_TITLE'	=> 'Настройки',

	'ACP_PHPBB_ADS_ADD_LOG'		=> '<strong>Рекламное объявление добавлено</strong><br />» %s',
	'ACP_PHPBB_ADS_EDIT_LOG'		=> '<strong>Рекламное объявление изменено</strong><br />» %s',
	'ACP_PHPBB_ADS_DELETE_LOG'	=> '<strong>Рекламное объявление удалено</strong><br />» %s',
));
