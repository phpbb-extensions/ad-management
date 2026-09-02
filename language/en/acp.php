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
	'AD_DETAILS'				=> 'Ad details',
	'AD_CONTENT'				=> 'Ad content',
	'AD_PRIVACY'				=> 'Privacy',
	'AD_VISIBILITY'				=> 'Placement and visibility',
	'AD_DELIVERY'				=> 'Delivery',
	'AD_TRACKING_AND_LIMITS'	=> 'Tracking and limits',
	'ACTIVE_ADS'				=> 'Current ads',
	'EXPIRED_ADS'				=> 'Expired ads',
	'STATUS'					=> 'Status',
	'AD_NAME'					=> 'Name',
	'AD_NAME_EXPLAIN'			=> 'The name is only used to help you identify this ad.',
	'AD_ENABLED'				=> 'Enabled',
	'AD_ENABLED_EXPLAIN'		=> 'If disabled, this ad will not be displayed.',
	'AD_NOTE'					=> 'Notes',
	'AD_NOTE_EXPLAIN'			=> 'Enter any notes for this ad. These notes are optional and only shown in the ACP.',
	'AD_CODE'					=> 'Ad code',
	'AD_CODE_EXPLAIN'			=> 'Enter the ad code here. All code must use HTML markup; BBCodes are not supported.<br><br>Note: If this ad places cookies, collects user data, or tracks user behaviour, enable <strong>Advertising Disclosure</strong> in the <samp class="error">Advertisement Management » Settings</samp> panel. If you are uncertain, enable it.',
	'AD_CODE_CONSENT_EXPLAIN'	=> ' Also enable <strong>Require marketing consent</strong> below so this ad’s scripts are deferred until the visitor allows marketing in their Privacy Settings.',
	'ANALYSE_AD_CODE'			=> 'Analyse ad code',
	'EVERYTHING_OK'				=> 'The code appears OK.',
	'AD_PREVIEW_BLOCKED'		=> 'Preview appears to be blocked',
	'AD_PREVIEW_BLOCKED_EXPLAIN' => 'A content blocker, ad blocker or browser privacy feature appears to have hidden this preview. Temporarily allow ads for this site and preview again.',
	'AD_PREVIEW_EMPTY'			=> 'No visible preview was produced',
	'AD_PREVIEW_EMPTY_EXPLAIN'	=> 'A content blocker, ad blocker or browser privacy feature may be blocking this ad. The ad code may also still be loading, be invalid, or not produce visible content. Temporarily allow ads for this site and preview again.',
	'BANNER'					=> 'Upload a banner',
	'BANNER_EXPLAIN'			=> 'You may upload an image in JPG, GIF or PNG format. The image will be stored in phpBB’s <samp>images</samp> directory, and an HTML IMG tag for the image will automatically be inserted into the ad code field.',
	'BANNER_UPLOAD'				=> 'Upload banner',
	'AD_LOCATIONS'				=> 'Locations',
	'AD_LOCATIONS_EXPLAIN'		=> 'Select locations where you want this ad displayed. Mouse over a location for a short description. If multiple ads use the same location, one will be selected at random each time the location is displayed. Use CTRL+CLICK (or CMD+CLICK on Mac) to select or deselect multiple locations.',
	'AD_LOCATIONS_VISUAL_DEMO'	=> 'Start visual demo of ad locations',
	'VISUAL_DEMO_EXPLAIN'		=> 'Open your forum with sample ads in every location. The demo is visible only to you and remains active until you disable it.',
	'AD_PRIORITY'				=> 'Priority',
	'AD_PRIORITY_EXPLAIN'		=> 'Set a number between 1 and 10. Ads with higher numbers are displayed more often when multiple ads use the same location.',
	'AD_CONTENT_ONLY'			=> 'Display on content pages only',
	'AD_CONTENT_ONLY_EXPLAIN'	=> 'This ad will only display on pages that contain content. It will not be shown on pages such as the UCP, login, registration, posting, or replying. Some advertising platforms (e.g. Google AdSense) require this.',
	'AD_OWNER'					=> 'Assigned user',
	'AD_OWNER_EXPLAIN'			=> 'Assign this ad to a board member. The assigned user can monitor its view and click statistics in their User Control Panel and receive notifications if it is disabled after reaching its end date, view limit, or click limit. Leave this field blank to not assign a user.',
	'AD_VIEWS'					=> 'Views',
	'AD_VIEWS_LIMIT'			=> 'View limit',
	'AD_VIEWS_LIMIT_EXPLAIN'	=> 'Set the maximum number of times this ad can be displayed. Once reached, the ad will be disabled. Set to 0 for unlimited views.',
	'AD_CLICKS'					=> 'Clicks',
	'AD_CLICKS_LIMIT'			=> 'Click limit',
	'AD_CLICKS_LIMIT_EXPLAIN'	=> 'Set the maximum number of times this ad can be clicked. Once reached, the ad will be disabled. Set to 0 for unlimited clicks.',
	'AD_CONSENT'				=> 'Require marketing consent',
	'AD_CONSENT_EXPLAIN'		=> 'Set to Yes to defer script tags in this ad until the visitor grants marketing consent in Privacy Settings. Set to No only for ad code that does not load marketing, tracking, cookies, profiling, or other consent-controlled resources.<br><br>Note: This setting has no effect on supported Google AdSense or Google Publisher Tag (GPT) code. Consent Manager automatically manages consent for Google Ads through Google Consent Mode.',
	'AD_START_DATE'				=> 'Start date',
	'AD_START_DATE_EXPLAIN'		=> 'Set the date when the ad can begin displaying (starting at 00:00). The ad must still be manually enabled to appear. If no date is set, the ad can display immediately once enabled.',
	'AD_END_DATE'				=> 'End date',
	'AD_END_DATE_EXPLAIN'		=> 'Set the date when the ad will automatically stop displaying (at 00:00). If no date is set, the ad will stay active until manually disabled.',
	'AD_CENTERING'				=> 'Center this ad automatically',
	'AD_CENTERING_EXPLAIN'		=> 'Set to Yes to let this extension center the ad automatically. If this produces undesired results, use CSS in the ad code instead.',

	'AD_ENABLE_TITLE'			=> array( // Plural rule doesn't apply here! Just translate the values.
		0 => 'Click to enable',
		1 => 'Click to disable',
	),
	'AD_EXPIRED_EXPLAIN'		=> 'This ad has expired and has been disabled.',
	'ACP_ADS_EMPTY'				=> 'No ads to display. Add one using the button below.',
	'ACP_ADS_ADD'				=> 'Add new ad',
	'ACP_ADS_EDIT'				=> 'Edit ad',

	'AD_NAME_REQUIRED'			=> 'Name is required.',
	'AD_NAME_TOO_LONG'			=> 'Name length is limited to %d characters.',
	'AD_CODE_ILLEGAL_CHARS'		=> 'Ad code contains the following unsupported characters: %s',
	'AD_START_DATE_INVALID'		=> 'The start date is invalid or is before today.',
	'AD_END_DATE_INVALID'		=> 'The end date is invalid or is before today.',
	'AD_PRIORITY_INVALID'		=> 'The priority is invalid. Please set a number between 1 and 10.',
	'AD_VIEWS_LIMIT_INVALID'	=> 'The views limit is invalid. Please set a non-negative number.',
	'AD_CLICKS_LIMIT_INVALID'	=> 'The clicks limit is invalid. Please set a non-negative number.',
	'AD_OWNER_INVALID'			=> 'The assigned user is invalid. Please select a user using the Find a member link.',
	'NO_FILE_SELECTED'			=> 'No file selected.',
	'CANNOT_CREATE_DIRECTORY'	=> 'The <samp>phpbb_ads</samp> directory could not be created. Please make sure the <samp>/images</samp> directory is writable.',
	'FILE_MOVE_UNSUCCESSFUL'	=> 'Unable to move the file to <samp>images/phpbb_ads</samp>.',
	'END_DATE_TOO_SOON'			=> 'The end date must be later than the start date.',
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
	'UNSECURE_CONNECTION'	=> '<strong>Mixed Content</strong><br>Your board runs on a secure HTTPS connection; however, the ad code is attempting to load content from an insecure HTTP connection. This can cause browsers to generate a “Mixed Content” warning to let users know that the page contains insecure resources.',
	'SCRIPT_WITHOUT_ASYNC'	=> '<strong>Non-asynchronous javascript</strong><br>This ad code loads JavaScript in a non-asynchronous way. This blocks other JavaScript from loading until it has finished and can affect page-load performance. Using the <samp>async</samp> attribute can improve page-load speed.',
	'MARKETING_CONSENT_RECOMMENDED'	=> '<strong>Require marketing consent</strong><br>This ad contains executable <samp>&lt;script&gt;</samp> tags. If it loads marketing, tracking, cookies, or other consent-controlled resources, ensure <strong>Require marketing consent</strong> is enabled below so its scripts are deferred until the visitor allows marketing in Privacy Settings.',
	'MARKETING_CONSENT_VENDOR_RECOMMENDED'	=> '<strong>Known ad vendor detected</strong><br>This ad contains executable <samp>&lt;script&gt;</samp> tags from a known advertising or marketing vendor. Ensure <strong>Require marketing consent</strong> is enabled below so its scripts are deferred until the visitor allows marketing in Privacy Settings.',
	'MARKETING_REVIEW_RECOMMENDED'	=> '<strong>Review consent requirements</strong><br>This ad contains executable <samp>&lt;script&gt;</samp> tags. If it loads marketing, tracking, cookies, or other consent-controlled resources, review the ad code and your privacy settings to ensure it complies with your user privacy policies.',
	'MARKETING_VENDOR_REVIEW_RECOMMENDED'	=> '<strong>Known ad vendor detected</strong><br>This ad contains executable <samp>&lt;script&gt;</samp> tags from a known advertising or marketing vendor. Review the ad code and your privacy settings to ensure it complies with your user privacy policies.',
	'ALERT_USAGE'			=> '<strong>Usage of <samp>alert()</samp></strong><br>Your code uses the <samp>alert()</samp> function which is not a good practice and can distract users. Some browsers may also block page load and display additional warnings to the user.',
	'LOCATION_CHANGE'		=> '<strong>Redirection</strong><br>Your code appears able to redirect a user to another page or site. Redirects can send users to unintended or malicious destinations. Verify the integrity of the ad code’s redirection destination.',
	'IFRAME_USAGE'			=> '<strong>Usage of <samp>&lt;iframe&gt;</samp></strong><br>Your code contains HTML-encoded <samp>&lt;iframe&gt;</samp> tags. Because iframes can introduce third-party tracking or data collection, review this ad code to ensure it complies with your user privacy policies.',

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
	'ADBLOCKER_MESSAGE_EXPLAIN'		=> 'This feature attempts to detect ad blockers and can ask or require detected visitors to disable ad blocking on this forum. Detection is not guaranteed: ad blockers, browser privacy features, and filter lists change frequently, so blockers may go undetected or visitors may be incorrectly flagged. The “Require” option restricts forum access only when ad blocking is detected; it cannot guarantee that ads will load.',
	'ADBLOCKER_MODES'				=> [
		0 => 'Allow ad blockers',
		1 => 'Ask visitors to disable ad blockers',
		2 => 'Require visitors to disable ad blockers',
	],
	'PRIVACY_LEGEND'				=> 'Privacy',
	'ENABLE_VIEWS'					=> 'Count views',
	'ENABLE_VIEWS_EXPLAIN'			=> 'Count how many times this ad is viewed. Multiple placements on one page count as one view. For anti-abuse protection, repeated views from the same network address within 10 seconds count as one view.',
	'ENABLE_CLICKS'					=> 'Count clicks',
	'ENABLE_CLICKS_EXPLAIN'			=> 'Count how many times this ad is clicked. For anti-abuse protection, repeated clicks from the same network address within 10 seconds count as one click.',
	'SHOW_AGREEMENT'				=> 'Advertising disclosure',
	'SHOW_AGREEMENT_EXPLAIN'		=> 'Show details in the Privacy Policy about how third-party advertising and tracking technologies are used on this forum. This disclosure must be enabled if advertisements on your forum collect or track user information.',
	'HIDE_GROUPS'					=> 'Hide ad from group members',
	'HIDE_GROUPS_EXPLAIN'			=> 'A user will not see this ad if they belong to any selected group. This includes both their default group and any additional groups. For example, selecting “Registered users” can also hide the ad from administrators and moderators who belong to that group. Use CTRL+CLICK (or CMD+CLICK on Mac) to select or deselect multiple groups.',

	'ACP_AD_SETTINGS_SAVED'	=> 'Advertisement management settings saved.',
));
