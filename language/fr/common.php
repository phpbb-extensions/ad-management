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
	'ADBLOCKER_TITLE'	=> 'Bloqueur de publicités détecté',
	'ADBLOCKER_MESSAGE'	=> 'Notre site Web est conçu pour afficher des publicités en ligne à nos visiteurs. Merci de considérer l’importance de l’affichage des publicités sur notre site Web en désactivant votre logiciel antipublicitaire sur notre forum.',
));
