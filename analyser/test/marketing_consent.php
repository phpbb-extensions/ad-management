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

	/**
	 * Google ad/tag scripts that support Google Consent Mode.
	 *
	 * Ads extension does not recommend script deferral for these tags because
	 * Consent Manager communicates the marketing consent state through Google
	 * Consent Mode instead.
	 */
	protected const GOOGLE_CONSENT_AWARE_SCRIPT_SOURCE_PATTERNS = array(
		'~(^|[/.])pagead2\.googlesyndication\.com/pagead/js/adsbygoogle\.js(?:[?#]|$)~i',
		'~(^|[/.])securepubads\.g\.doubleclick\.net/tag/js/gpt\.js(?:[?#]|$)~i',
		'~(^|[/.])www\.googletagservices\.com/tag/js/gpt\.js(?:[?#]|$)~i',
		'~(^|[/.])www\.googletagmanager\.com/(?:gtag/js|gtm\.js)(?:[?#]|$)~i',
	);

	/** @var \phpbb\config\config */
	protected $config;

	/**
	 * @param \phpbb\config\config $config Config object
	 */
	public function __construct(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Suggest enabling Require marketing consent when executable script tags are
	 * present, Consent Manager marketing is available, and the ad-level consent
	 * toggle is currently disabled.
	 */
	public function run($ad_code, array $context = array())
	{
		if (!$this->config->offsetExists('consentmanager_marketing_enabled')
			|| empty($this->config['consentmanager_marketing_enabled'])
			|| !isset($context['ad_consent'])
			|| !empty($context['ad_consent']))
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

		$google_consent_aware_sources = $this->get_google_consent_aware_script_sources($ad_code);

		foreach ($matches[1] as $index => $attributes)
		{
			$content = $matches[2][$index] ?? '';
			if (!$this->should_flag_script_tag($attributes))
			{
				continue;
			}

			if ($this->is_google_consent_aware_script($attributes, $content, $google_consent_aware_sources))
			{
				continue;
			}

			if ($this->contains_marketing_host_hint($attributes, $content))
			{
				return 'MARKETING_CONSENT_VENDOR_RECOMMENDED';
			}

			return 'MARKETING_CONSENT_RECOMMENDED';
		}

		return false;
	}

	/**
	 * Determine whether a script should run under Google Consent Mode.
	 *
	 * @param string $attributes Script tag attributes
	 * @param string $content Script tag content
	 * @param array $google_consent_aware_sources Known Google loader sources in this ad block
	 * @return bool
	 */
	protected function is_google_consent_aware_script($attributes, $content, array $google_consent_aware_sources)
	{
		$source = $this->extract_script_source($attributes);
		if ($source !== '')
		{
			return isset($google_consent_aware_sources[$this->normalize_script_source($source)]);
		}

		return !empty($google_consent_aware_sources)
			&& preg_match('/\b(?:adsbygoogle|googletag|gtag|dataLayer)\b/', $content);
	}

	/**
	 * Return known Google Consent Mode-aware loader sources in an ad block.
	 *
	 * @param string $ad_code Advertisement code
	 * @return array
	 */
	protected function get_google_consent_aware_script_sources($ad_code)
	{
		$sources = array();

		if (!preg_match_all('#<script\b([^>]*)>#is', $ad_code, $matches))
		{
			return $sources;
		}

		foreach ($matches[1] as $attributes)
		{
			$source = $this->extract_script_source($attributes);
			if ($source !== '' && $this->is_google_consent_aware_script_source($source))
			{
				$sources[$this->normalize_script_source($source)] = true;
			}
		}

		return $sources;
	}

	/**
	 * Extract the src attribute from a script tag attribute string.
	 *
	 * @param string $attributes Script tag attributes
	 * @return string
	 */
	protected function extract_script_source($attributes)
	{
		return preg_match('/\bsrc\s*=\s*([\'"])(.*?)\1/i', $attributes, $matches) ? $matches[2] : '';
	}

	/**
	 * Check whether a script source is a known Google Consent Mode-aware loader.
	 *
	 * @param string $source Script source URL
	 * @return bool
	 */
	protected function is_google_consent_aware_script_source($source)
	{
		$source = $this->normalize_script_source($source);

		foreach (self::GOOGLE_CONSENT_AWARE_SCRIPT_SOURCE_PATTERNS as $pattern)
		{
			if (preg_match($pattern, $source))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Normalize a script source before comparing against allowlisted loaders.
	 *
	 * @param string $source Script source URL
	 * @return string
	 */
	protected function normalize_script_source($source)
	{
		return preg_replace('#^//#', 'https://', trim($source));
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

		$source = $this->extract_script_source($attributes);
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
