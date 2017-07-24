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
	// Manage ads
	'AD_NAME'					=> 'Name',
	'AD_NAME_EXPLAIN'			=> 'The name is only used to help you identify this advertisement.',
	'AD_ENABLED'				=> 'Enabled',
	'AD_ENABLED_EXPLAIN'		=> 'If disabled, this advertisement will not be displayed.',
	'AD_END_DATE'				=> 'End Date',
	'AD_END_DATE_EXPLAIN'		=> 'Set the date the advertisement will expire and become disabled. Leave this field blank if you do not want the advertisement to expire. Please use <samp>YYYY-MM-DD</samp> format.',
	'AD_VIEWS_LIMIT'			=> 'Views Limit',
	'AD_VIEWS_LIMIT_EXPLAIN'	=> 'Set the maximum number of times the ad will be displayed, after which the ad will no longer be displayed. Set 0 for unlimited views.',
	'AD_CLICKS_LIMIT'			=> 'Clicks Limit',
	'AD_CLICKS_LIMIT_EXPLAIN'	=> 'Set the maximum number of times the ad will be clicked, after which the ad will no longer be displayed. Set 0 for unlimited views.',
	'AD_PRIORITY'				=> 'Priority',
	'AD_PRIORITY_EXPLAIN'		=> 'Set a number between 1 and 10. Advertisements with higher number will be displayed more often.',
	'AD_NOTE'					=> 'Notes',
	'AD_NOTE_EXPLAIN'			=> 'Enter any notes for this advertisement. These notes are not shown anywhere except in the ACP.',
	'AD_CODE'					=> 'Code',
	'AD_CODE_EXPLAIN'			=> 'Enter the advertisement code here. All code must use HTML markup, BBCodes are not supported.',
	'AD_LOCATIONS'				=> 'Locations',
	'AD_LOCATIONS_EXPLAIN'		=> 'Select locations where you want this advertisement displayed. Mouse over a location for a short description of it. If multiple ads use the same location, one ad will be randomly selected to display in that location each time. Use CTRL+CLICK (or CMD+CLICK on Mac) to select/deselect more than one location.',
	'AD_VIEWS'					=> 'Views',
	'AD_CLICKS'					=> 'Clicks',
	'AD_ENABLE_TITLE'			=> array( // Plural rule doesn't apply here! Just translate the values.
		0 => 'Click to enable',
		1 => 'Click to disable',
	),
	'AD_EXPIRED_EXPLAIN'		=> 'This advertisement has expired and has been disabled.',
	'AD_OWNER'					=> 'Owner',
	'AD_OWNER_EXPLAIN'			=> 'The ad owner is a user who can view the advertisement views and clicks statistics in the User Control Panel.',
	'ACP_ADS_EMPTY'				=> 'No advertisements to display. Add one using the button below.',
	'ACP_ADS_ADD'				=> 'Add new advertisement',
	'ACP_ADS_EDIT'				=> 'Edit advertisement',
	'AD_PREVIEW'				=> 'Preview this advertisement',
	'CONFIGURE_AD'				=> 'Configure advertisement',
	'BANNER_UPLOAD'				=> 'Upload banner',

	'AD_NAME_REQUIRED'			=> 'Name is required.',
	'AD_NAME_TOO_LONG'			=> 'Name length is limited to %d characters.',
	'AD_END_DATE_INVALID'		=> 'The end date is invalid or has already expired.',
	'AD_PRIORITY_INVALID'		=> 'The priority is invalid. Please set a number between 1 and 10.',
	'AD_VIEWS_LIMIT_INVALID'	=> 'The views limit is invalid. Please set a non-negative number.',
	'AD_CLICKS_LIMIT_INVALID'	=> 'The clicks limit is invalid. Please set a non-negative number.',
	'AD_OWNER_INVALID'			=> 'The ad owner is invalid. Please select a user using the Find a member link.',
	'NO_FILE_SELECTED'			=> 'No file selected.',
	'ACP_AD_DOES_NOT_EXIST'		=> 'The advertisement does not exist.',
	'ACP_AD_ADD_SUCCESS'		=> 'Advertisement added successfully.',
	'ACP_AD_EDIT_SUCCESS'		=> 'Advertisement edited successfully.',
	'ACP_AD_DELETE_SUCCESS'		=> 'Advertisement deleted successfully.',
	'ACP_AD_DELETE_ERRORED'		=> 'There was an error deleting the advertisement.',
	'ACP_AD_ENABLE_SUCCESS'		=> 'Advertisement enabled successfully.',
	'ACP_AD_ENABLE_ERRORED'		=> 'There was an error enabling the advertisement.',
	'ACP_AD_DISABLE_SUCCESS'	=> 'Advertisement disabled successfully.',
	'ACP_AD_DISABLE_ERRORED'	=> 'There was an error disabling the advertisement.',

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

	// Settings
	'ADBLOCKER_MESSAGE'				=> 'Ad blocker detected message',
	'ADBLOCKER_MESSAGE_EXPLAIN'		=> 'Display a polite message to visitors using ad blockers, advising them to consider disabling ad blocking on this forum.',
	'ENABLE_VIEWS'					=> 'Count views',
	'ENABLE_VIEWS_EXPLAIN'			=> 'This will enable counting how many times every ad has been displayed. Note, that it adds extra load to the server, so if you do not need this feature, disable it.',
	'ENABLE_CLICKS'					=> 'Count clicks',
	'ENABLE_CLICKS_EXPLAIN'			=> 'This will enable counting how many times every ad has been clicked. Note, that it adds extra load to the server, so if you do not need this feature, disable it.',
	'HIDE_GROUPS'					=> 'Hide advertisements for groups',
	'HIDE_GROUPS_EXPLAIN'			=> 'Members of selected groups will not see any advertisement. Use CTRL+CLICK (or CMD+CLICK on Mac) to select/deselect more than one group.',

	'ACP_AD_SETTINGS_SAVED'	=> 'Advertisement management settings saved.',
));
