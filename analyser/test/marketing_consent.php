<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\analyser\test;

class marketing_consent implements test_interface
{
	/**
	 * Common advertising and marketing script hosts.
	 *
	 * Keep this list script-focused. Ads extension only defers script tags, so
	 * host hints should not expand warnings to iframe-only or image-only embeds.
	 */
	protected const MARKETING_HOST_PATTERNS = array(
		'/(^|[\/.])partner\.googleadservices\.com(?=[:\/]|$)/i',
		'/(^|[\/.])googleads\.g\.doubleclick\.net(?=[:\/]|$)/i',
		'/(^|[\/.])c\.amazon-adsystem\.com(?=[:\/]|$)/i',
		'/(^|[\/.])aax\.amazon-adsystem\.com(?=[:\/]|$)/i',
		'/(^|[\/.])trc\.taboola\.com(?=[:\/]|$)/i',
		'/(^|[\/.])cdn\.taboola\.com(?=[:\/]|$)/i',
		'/(^|[\/.])widgets\.outbrain\.com(?=[:\/]|$)/i',
		'/(^|[\/.])odr\.outbrain\.com(?=[:\/]|$)/i',
		'/(^|[\/.])static\.criteo\.net(?=[:\/]|$)/i',
		'/(^|[\/.])gum\.criteo\.com(?=[:\/]|$)/i',
		'/(^|[\/.])secure\.adnxs\.com(?=[:\/]|$)/i',
		'/(^|[\/.])ib\.adnxs\.com(?=[:\/]|$)/i',
	);

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\extension\manager */
	protected $extension_manager;

	/**
	 * @param \phpbb\config\config     $config            Config object
	 * @param \phpbb\extension\manager $extension_manager Extension manager object
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\extension\manager $extension_manager)
	{
		$this->config = $config;
		$this->extension_manager = $extension_manager;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Recommend reviewing consent requirements when executable script tags are present.
	 */
	public function run($ad_code)
	{
		if (!$this->config->offsetExists('consentmanager_marketing_enabled')
			|| empty($this->config['consentmanager_marketing_enabled']))
		{
			return false;
		}

		$decoded = htmlspecialchars_decode($ad_code, ENT_COMPAT);
		$message = $this->get_recommendation_message($decoded);
		if ($message === false)
		{
			return false;
		}

		return array(
			'severity'	=> 'notice',
			'message'	=> $message,
		);
	}

	/**
	 * Get consent recommendation message for ad code, if any.
	 *
	 * @param string $ad_code Advertisement code
	 * @return string|false
	 */
	protected function get_recommendation_message($ad_code)
	{
		if (!preg_match_all('#<script\b([^>]*)>(.*?)</script\s*>#is', $ad_code, $matches))
		{
			return false;
		}

		$google_consent_aware_sources = \phpbb\ads\ad\manager::get_google_consent_aware_script_sources($ad_code);
		$consent_manager_available = $this->is_consent_manager_available();

		foreach ($matches[1] as $index => $attributes)
		{
			$content = $matches[2][$index] ?? '';
			if (!$this->should_flag_script_tag($attributes))
			{
				continue;
			}

			if (\phpbb\ads\ad\manager::is_google_consent_aware_script($attributes, $content, $google_consent_aware_sources))
			{
				continue;
			}

			if ($this->contains_marketing_host_hint($attributes, $content))
			{
				return $consent_manager_available ? 'MARKETING_CONSENT_VENDOR_RECOMMENDED' : 'MARKETING_VENDOR_REVIEW_RECOMMENDED';
			}

			return $consent_manager_available ? 'MARKETING_CONSENT_RECOMMENDED' : 'MARKETING_REVIEW_RECOMMENDED';
		}

		return false;
	}

	/**
	 * Check whether Consent Manager marketing controls are available.
	 *
	 * @return bool
	 */
	protected function is_consent_manager_available()
	{
		return $this->extension_manager->is_enabled('phpbb/consentmanager')
			&& $this->config->offsetExists('consentmanager_marketing_enabled')
			&& (bool) $this->config['consentmanager_marketing_enabled'];
	}

	/**
	 * Check for known advertising vendor hints inside script markup or content.
	 *
	 * @param string $attributes Script tag attributes
	 * @param string $content Script tag content
	 * @return bool
	 */
	protected function contains_marketing_host_hint($attributes, $content)
	{
		$haystacks = array($attributes, $content);

		$source = \phpbb\ads\ad\manager::extract_script_source($attributes);
		if ($source !== '')
		{
			$haystacks[] = $source;
		}

		foreach ($haystacks as $haystack)
		{
			foreach (self::MARKETING_HOST_PATTERNS as $pattern)
			{
				if (preg_match($pattern, $haystack))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Mirror ads defer logic closely enough to avoid flagging inert script types.
	 *
	 * @param string $attributes Script tag attributes
	 * @return bool
	 */
	protected function should_flag_script_tag($attributes)
	{
		if (preg_match('/\bdata-consent-category\s*=/i', $attributes))
		{
			return false;
		}

		if (!preg_match('/\btype\s*=\s*([\'"])(.*?)\1/i', $attributes, $matches))
		{
			return true;
		}

		$type = strtolower(trim(explode(';', $matches[2])[0]));
		return $type === ''
			|| $type === 'module'
			|| strpos($type, 'javascript') !== false
			|| strpos($type, 'ecmascript') !== false;
	}
}
