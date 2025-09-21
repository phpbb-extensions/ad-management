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
	'AD_SETTINGS'				=> 'Advertisement settings',
	'ACTIVE_ADS'				=> 'Active ads',
	'EXPIRED_ADS'				=> 'Expired ads',
	'STATUS'					=> 'Status',
	'AD_NAME'					=> 'Name',
	'AD_NAME_EXPLAIN'			=> 'The name is only used to help you identify this advertisement.',
	'AD_ENABLED'				=> 'Enabled',
	'AD_ENABLED_EXPLAIN'		=> 'If disabled, this advertisement will not be displayed.',
	'AD_NOTE'					=> 'Notes',
	'AD_NOTE_EXPLAIN'			=> 'Enter any notes for this advertisement. These notes are not shown anywhere except in the ACP and are optional.',
	'AD_CODE'					=> 'Code',
	'AD_CODE_EXPLAIN'			=> 'Enter the advertisement code here. All code must use HTML markup, BBCodes are not supported.<br><br>Note: If your ad code places cookies, collects user data, or tracks user behaviour (for example, ads from Google AdSense or other third-party ad networks), then you should enable the <strong>Advertising Disclosure</strong> in the <strong>Advertisement Management Settings</strong> panel to ensure compliance. If you are uncertain, it is recommended that you enable it.',
	'ANALYSE_AD_CODE'			=> 'Analyse advertisement code',
	'EVERYTHING_OK'				=> 'The code appears OK.',
	'AD_BANNER'					=> 'Advertisement banner',
	'BANNER'					=> 'Upload a banner',
	'BANNER_EXPLAIN'			=> 'You may upload an image in JPG, GIF or PNG format. The image will be stored in phpBB‘s <samp>images</samp> directory and an HTML IMG tag for the image will automatically be inserted into the ad code field.',
	'BANNER_UPLOAD'				=> 'Upload banner',
	'AD_PLACEMENT'				=> 'Advertisement placement',
	'AD_LOCATIONS'				=> 'Locations',
	'AD_LOCATIONS_EXPLAIN'		=> 'Select locations where you want this advertisement displayed. Mouse over a location for a short description of it. If multiple ads use the same location, one ad will be randomly selected to display in that location each time. Use CTRL+CLICK (or CMD+CLICK on Mac) to select/deselect more than one location.',
	'AD_LOCATIONS_VISUAL_DEMO'	=> 'Start visual demo of ad locations',
	'VISUAL_DEMO_EXPLAIN'		=> 'Start the visual demo to open your forum in a new browser window with sample ads in every location. Only you will see the demo, your visitors will see your forum normally. You must deactivate the demo when you are done viewing it (or it will persist for you as you use your forum). The “Click to disable visual demo” button will be available on every page.',
	'AD_PRIORITY'				=> 'Priority',
	'AD_PRIORITY_EXPLAIN'		=> 'Set a number between 1 and 10. Advertisements with higher number will be displayed more often when there are multiple ads using the same location.',
	'AD_CONTENT_ONLY'			=> 'Display on content pages only',
	'AD_CONTENT_ONLY_EXPLAIN'	=> 'This ad will only display on pages that contain content. It will not be shown on pages such as the UCP, login, registration, posting, replying, etc. Some advertising platforms (e.g. Google AdSense) require this.',
	'AD_OPTIONS'				=> 'Advertisement options',
	'AD_OWNER'					=> 'Owner',
	'AD_OWNER_EXPLAIN'			=> 'Assigning an ad owner will give one of your board members permission to view this advertisement‘s view and click statistics in their User Control Panel. Leave this field blank to not assign an ad owner.',
	'AD_VIEWS'					=> 'Views',
	'AD_VIEWS_LIMIT'			=> 'Views Limit',
	'AD_VIEWS_LIMIT_EXPLAIN'	=> 'Set the maximum number of times the ad will be displayed, after which the ad will no longer be displayed. Set 0 for unlimited views.',
	'AD_CLICKS'					=> 'Clicks',
	'AD_CLICKS_LIMIT'			=> 'Clicks Limit',
	'AD_CLICKS_LIMIT_EXPLAIN'	=> 'Set the maximum number of times the ad will be clicked, after which the ad will no longer be displayed. Set 0 for unlimited clicks.',
	'AD_START_DATE'				=> 'Start Date',
	'AD_START_DATE_EXPLAIN'		=> 'Set the date the advertisement will start and become enabled. Leave this field blank if you do not want the advertisement to start automatically in the future. Please use <samp>YYYY-MM-DD</samp> format.',
	'AD_END_DATE'				=> 'End Date',
	'AD_END_DATE_EXPLAIN'		=> 'Set the date the advertisement will expire and become disabled. Leave this field blank if you do not want the advertisement to expire. Please use <samp>YYYY-MM-DD</samp> format.',
	'AD_CENTERING'				=> 'Center this ad automatically',
	'AD_CENTERING_EXPLAIN'		=> 'Set to yes to let this extension center your ad automatically. If this leads to undesired results, use CSS directly in the code to center your ad accordingly.',

	'AD_PREVIEW'				=> 'Preview this advertisement',
	'AD_ENABLE_TITLE'			=> array( // Plural rule doesn't apply here! Just translate the values.
		0 => 'Click to enable',
		1 => 'Click to disable',
	),
	'AD_EXPIRED_EXPLAIN'		=> 'This advertisement has expired and has been disabled.',
	'ACP_ADS_EMPTY'				=> 'No advertisements to display. Add one using the button below.',
	'ACP_ADS_ADD'				=> 'Add new advertisement',
	'ACP_ADS_EDIT'				=> 'Edit advertisement',

	'AD_NAME_REQUIRED'			=> 'Name is required.',
	'AD_NAME_TOO_LONG'			=> 'Name length is limited to %d characters.',
	'AD_CODE_ILLEGAL_CHARS'		=> 'Ad code contains the following unsupported characters: %s',
	'AD_START_DATE_INVALID'		=> 'The start date is invalid or has already expired.',
	'AD_END_DATE_INVALID'		=> 'The end date is invalid or has already expired.',
	'AD_PRIORITY_INVALID'		=> 'The priority is invalid. Please set a number between 1 and 10.',
	'AD_VIEWS_LIMIT_INVALID'	=> 'The views limit is invalid. Please set a non-negative number.',
	'AD_CLICKS_LIMIT_INVALID'	=> 'The clicks limit is invalid. Please set a non-negative number.',
	'AD_OWNER_INVALID'			=> 'The ad owner is invalid. Please select a user using the Find a member link.',
	'NO_FILE_SELECTED'			=> 'No file selected.',
	'CANNOT_CREATE_DIRECTORY'	=> 'The <samp>phpbb_ads</samp> directory could not be created. Please make sure the <samp>/images</samp> directory is writable.',
	'FILE_MOVE_UNSUCCESSFUL'	=> 'Unable to move file to <samp>images/phpbb_ads</samp>.',
	'END_DATE_TOO_SOON'			=> 'End date is sooner than start date.',
	'ACP_AD_DOES_NOT_EXIST'		=> 'The advertisement does not exist.',
	'ACP_AD_ADD_SUCCESS'		=> 'Advertisement added successfully.',
	'ACP_AD_EDIT_SUCCESS'		=> 'Advertisement edited successfully.',
	'ACP_AD_DELETE_SUCCESS'		=> 'Advertisement deleted successfully.',
	'ACP_AD_DELETE_ERRORED'		=> 'There was an error deleting the advertisement.',
	'ACP_AD_ENABLE_SUCCESS'		=> 'Advertisement enabled successfully.',
	'ACP_AD_ENABLE_ERRORED'		=> 'There was an error enabling the advertisement.',
	'ACP_AD_DISABLE_SUCCESS'	=> 'Advertisement disabled successfully.',
	'ACP_AD_DISABLE_ERRORED'	=> 'There was an error disabling the advertisement.',

	// Analyser tests
	'UNSECURE_CONNECTION'	=> '<strong>Mixed Content</strong><br />Your board runs on a secure HTTPS connection, however the ad code is attempting to load content from an insecure HTTP connection. This can cause browsers to generate a “Mixed Content” warning to let users know that the page contains insecure resources.',
	'SCRIPT_WITHOUT_ASYNC'	=> '<strong>Non-asynchronous javascript</strong><br />This ad code loads JavaScript code in a non-asynchronous way. This means it will block any other Javascript from loading until it has completed loading, which can affect page load performance. Use of the <samp>async</samp> attribute can speed up the page load.',
	'ALERT_USAGE'			=> '<strong>Usage of <samp>alert()</samp></strong><br />Your code uses the <samp>alert()</samp> function which is not a good practice and can distract users. Some browsers may also block page load and display additional warnings to the user.',
	'LOCATION_CHANGE'		=> '<strong>Redirection</strong><br />Your code appears it can redirect user to another page or site. Redirects can sometimes send users to unintended, often malicious, destinations. Please verify the integrity of your ad code’s redirection destination.',

	// Template location categories
	'CAT_TOP_OF_PAGE'		=> 'Top of page',
	'CAT_BOTTOM_OF_PAGE'	=> 'Bottom of page',
	'CAT_IN_POSTS'			=> 'In posts',
	'CAT_OTHER'				=> 'Other',
	'CAT_INTERACTIVE'		=> 'Interactive',
	'CAT_SPECIAL'			=> 'Special',

	// Settings
	'ADBLOCKER_LEGEND'				=> 'Ad Blockers',
	'ADBLOCKER_MESSAGE'				=> 'Ad blocker detected message',
	'ADBLOCKER_MESSAGE_EXPLAIN'		=> 'Display a message to visitors using ad blockers, asking or requiring them to disable ad blocking on this forum. If requiring visitors to disable ad blockers, they will not be able to use the forum until they have disabled their ad blocker.',
	'ADBLOCKER_MODES'				=> [
		0 => 'Allow ad blockers',
		1 => 'Ask visitors to disable ad blockers',
		2 => 'Require visitors to disable ad blockers',
	],
	'CLICKS_VIEWS_LEGEND'			=> 'Statistics and Tracking',
	'ENABLE_VIEWS'					=> 'Count views',
	'ENABLE_VIEWS_EXPLAIN'			=> 'This will enable counting how many times every ad has been displayed. Note, that it adds extra load to the server, so if you do not need this feature, disable it.',
	'ENABLE_CLICKS'					=> 'Count clicks',
	'ENABLE_CLICKS_EXPLAIN'			=> 'This will enable counting how many times every ad has been clicked. Note, that it adds extra load to the server, so if you do not need this feature, disable it.',
	'SHOW_AGREEMENT'				=> 'Advertising disclosure',
	'SHOW_AGREEMENT_EXPLAIN'		=> 'Show details in the Privacy Policy about how third-party advertising and tracking technologies are used on this forum. This disclosure must be enabled if advertisements on your forum collect or track user information.',
	'HIDE_GROUPS'					=> 'Hide advertisement for groups',
	'HIDE_GROUPS_EXPLAIN'			=> 'Members of selected groups will not see this advertisement. Use CTRL+CLICK (or CMD+CLICK on Mac) to select/deselect more than one group.',

	'ACP_AD_SETTINGS_SAVED'	=> 'Advertisement management settings saved.',
));
