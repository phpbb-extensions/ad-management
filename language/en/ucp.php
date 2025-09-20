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
	'AD_NAME'		=> 'Name',
	'AD_START_DATE'	=> 'Start Date',
	'AD_END_DATE'	=> 'End Date',
	'AD_VIEWS'		=> 'Views',
	'AD_CLICKS'		=> 'Clicks',
	'AD_STATUS'		=> 'Status',
	'EXPIRED'		=> 'Expired',
	'ACTIVE_ADS'	=> 'Active ads',
	'EXPIRED_ADS'	=> 'Expired ads',
	'NO_ADS'		=> '<strong>You do not have any advertisements displayed on this board.</strong>',

	'PHPBB_ADS_PRIVACY_POLICY' => '
		<br><br>
		<h3>Advertising</h3>
		“%1$s” may display advertising provided by third-party networks or services. These advertisements may use cookies, tracking pixels, or similar technologies to collect information about your browsing activities on this site and, in some cases, across other websites. This information may be used to deliver relevant advertisements, measure the effectiveness of advertising campaigns, and tailor content to your interests.
		<br><br>
		Any data collected through such third-party advertising services is subject to the privacy and cookie policies of the respective providers. “%1$s” does not control the cookies or data collection practices of these third parties. We encourage you to review the policies of the relevant advertising providers for more details on how your information is processed.
	',
));
