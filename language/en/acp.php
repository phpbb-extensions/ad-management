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
	'AD_ENABLED_EXPLAIN'	=> 'If disabled, this advertisement will not be displayed to this board users.',
	'AD_ENABLE_TITLE'		=> array( // Plural rule doesn't apply here! Just translate the values.
		0 => 'Click to enable',
		1 => 'Click to disable',
	),
	'ACP_ADS_EMPTY'		=> 'No advertisement, yet. Add one with the button below.',
	'ACP_ADS_ADD'		=> 'Add new advertisement',

	'AD_NAME_REQUIRED'		=> 'Name is required.',
	'AD_NAME_TOO_LONG'		=> 'Name length is limited to 255 characters.',
	'ACP_AD_ADD_SUCCESS'	=> 'Advertisement added successfully!',
));
