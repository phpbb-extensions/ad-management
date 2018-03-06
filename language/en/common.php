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

	'VISUAL_DEMO'			=> 'Visual demo for ad locations is active',
	'DISABLE_VISUAL_DEMO'	=> 'Click to disable visual demo.',
	'DISABLE_VISUAL_DEMO_ERROR'	=> 'There was a problem completing your request. Please try to disable the visual demo again.',

	// Template locations
	'AD_ABOVE_HEADER'				=> 'Above header',
	'AD_ABOVE_HEADER_DESC'			=> 'Displays on every page before the page header.',
	'AD_BELOW_HEADER'				=> 'Below header',
	'AD_BELOW_HEADER_DESC'			=> 'Displays on every page after the page header (and before navbar).',
	'AD_BEFORE_POSTS'				=> 'Before posts',
	'AD_BEFORE_POSTS_DESC'			=> 'Displays on topic page before the first post.',
	'AD_AFTER_POSTS'				=> 'After posts',
	'AD_AFTER_POSTS_DESC'			=> 'Displays on topic page after the last post.',
	'AD_BELOW_FOOTER'				=> 'Below footer',
	'AD_BELOW_FOOTER_DESC'			=> 'Displays on every page after the page footer.',
	'AD_ABOVE_FOOTER'				=> 'Above footer',
	'AD_ABOVE_FOOTER_DESC'			=> 'Displays on every page before the page footer.',
	'AD_AFTER_FIRST_POST'			=> 'After first post',
	'AD_AFTER_FIRST_POST_DESC'		=> 'Displays on topic page after the first post.',
	'AD_AFTER_NOT_FIRST_POST'		=> 'After every post except first',
	'AD_AFTER_NOT_FIRST_POST_DESC'	=> 'Displays on topic page after every post except the first post.',
	'AD_BEFORE_PROFILE'				=> 'Before user profile',
	'AD_BEFORE_PROFILE_DESC'		=> 'Displays before member profile page content.',
	'AD_AFTER_PROFILE'				=> 'After user profile',
	'AD_AFTER_PROFILE_DESC'			=> 'Displays after member profile page content.',
	'AD_AFTER_HEADER_NAVBAR'		=> 'After header navbar',
	'AD_AFTER_HEADER_NAVBAR_DESC'	=> 'Displays on every page after header navigation bar.',
	'AD_AFTER_FOOTER_NAVBAR'		=> 'After footer navbar',
	'AD_AFTER_FOOTER_NAVBAR_DESC'	=> 'Displays on every page after footer navigation bar.',
	'AD_POP_UP'						=> 'Pop-up',
	'AD_POP_UP_DESC'				=> 'Displays once per day when user visits this board as overlaying box. User need to close this box to continue to the content. Please, be aware, that this kind of advertisement is very obtrusive to the user!',
	'AD_SLIDE_UP'					=> 'Slide up',
	'AD_SLIDE_UP_DESC'				=> 'Displays on every page after user scrolls below main content. Slides up from the bottom.',
));
