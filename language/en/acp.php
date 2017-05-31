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
	'AD_NAME'				=> 'Name',
	'AD_NAME_EXPLAIN'		=> 'This is only used for your recognition of this advertisement.',
	'AD_NOTE'				=> 'Notes',
	'AD_NOTE_EXPLAIN'		=> 'Enter any notes for this advertisement. These notes are not shown anywhere except in the ACP.',
	'AD_CODE'				=> 'Code',
	'AD_CODE_EXPLAIN'		=> 'The advertisement code goes here. All code should be put in a raw HTML form, BBcodes are not supported.',
	'AD_ENABLED'			=> 'Enabled',
	'AD_ENABLED_EXPLAIN'	=> 'If disabled, this advertisement will not be displayed.',
	'AD_LOCATIONS'			=> 'Locations',
	'AD_LOCATIONS_EXPLAIN'	=> 'Select locations to display this advertisement in. In case multiple ads are assigned the same location, only a random one will be displayed at a time. Mouse over the location to see further description.',
	'AD_ENABLE_TITLE'		=> array( // Plural rule doesn't apply here! Just translate the values.
		0 => 'Click to enable',
		1 => 'Click to disable',
	),
	'ACP_ADS_EMPTY'		=> 'No advertisement, yet. Add one with the button below.',
	'ACP_ADS_ADD'		=> 'Add new advertisement',
	'ACP_ADS_EDIT'		=> 'Edit advertisement',
	'AD_PREVIEW'		=> 'Preview this advertisement',

	'AD_NAME_REQUIRED'			=> 'Name is required.',
	'AD_NAME_TOO_LONG'			=> 'Name length is limited to %d characters.',
	'ACP_AD_DOES_NOT_EXIST'		=> 'The advertisement does not exist!',
	'ACP_AD_ADD_SUCCESS'		=> 'Advertisement added successfully!',
	'ACP_AD_EDIT_SUCCESS'		=> 'Advertisement edited successfully!',
	'ACP_AD_DELETE_SUCCESS'		=> 'Advertisement deleted successfully!',
	'ACP_AD_DELETE_ERRORED'		=> 'There was an error deleting the advertisement!',
	'ACP_AD_ENABLE_SUCCESS'		=> 'Advertisement enabled successfully!',
	'ACP_AD_ENABLE_ERRORED'		=> 'There was an error enabling the advertisement!',
	'ACP_AD_DISABLE_SUCCESS'	=> 'Advertisement disabled successfully!',
	'ACP_AD_DISABLE_ERRORED'	=> 'There was an error disabling the advertisement!',

	// Template locations
	'AD_ABOVE_HEADER'				=> 'Above header',
	'AD_ABOVE_HEADER_DESC'			=> 'Displays on every page before page header.',
	'AD_BELOW_HEADER'				=> 'Below header',
	'AD_BELOW_HEADER_DESC'			=> 'Displays on every page after page header.',
	'AD_BEFORE_POSTS'				=> 'Before posts',
	'AD_BEFORE_POSTS_DESC'			=> 'Displays on topic page before first post.',
	'AD_AFTER_POSTS'				=> 'After posts',
	'AD_AFTER_POSTS_DESC'			=> 'Displays on topic page after last post.',
	'AD_BELOW_FOOTER'				=> 'Below footer',
	'AD_BELOW_FOOTER_DESC'			=> 'Displays on every page after page footer.',
	'AD_ABOVE_FOOTER'				=> 'Above footer',
	'AD_ABOVE_FOOTER_DESC'			=> 'Displays on every page before page footer.',
	'AD_AFTER_FIRST_POST'			=> 'After first post',
	'AD_AFTER_FIRST_POST_DESC'		=> 'Displays on topic page after first post.',
	'AD_AFTER_NOT_FIRST_POST'		=> 'After every post except first',
	'AD_AFTER_NOT_FIRST_POST_DESC'	=> 'Displays on topic page after every post except first.',
	'AD_BEFORE_PROFILE'				=> 'Before user profile',
	'AD_BEFORE_PROFILE_DESC'		=> 'Displays before member profile page content.',
	'AD_AFTER_PROFILE'				=> 'After user profile',
	'AD_AFTER_PROFILE_DESC'			=> 'Displays after member profile page content.',
));
