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
	'AD_ABOVE_HEADER'		=> 'Above header',
	'AD_ABOVE_HEADER_DESC'	=> 'Displays on every page before page header.',
	'AD_BELOW_HEADER'		=> 'Below header',
	'AD_BELOW_HEADER_DESC'	=> 'Displays on every page after page header.',
));
