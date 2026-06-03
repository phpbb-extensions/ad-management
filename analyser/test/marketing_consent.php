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
		'/(^|[\/.])pagead2\.googlesyndication\.com(?=[:\/]|$)/i',
		'/(^|[\/.])partner\.googleadservices\.com(?=[:\/]|$)/i',
		'/(^|[\/.])googleads\.g\.doubleclick\.net(?=[:\/]|$)/i',
		'/(^|[\/.])securepubads\.g\.doubleclick\.net(?=[:\/]|$)/i',
		'/(^|[\/.])www\.googletagservices\.com(?=[:\/]|$)/i',
		'/(^|[\/.])www\.googletagmanager\.com(?=[:\/]|$)/i',
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

		foreach ($matches[1] as $index => $attributes)
		{
			if (!$this->should_flag_script_tag($attributes))
			{
				continue;
			}

			$content = $matches[2][$index] ?? '';
			if ($this->contains_marketing_host_hint($attributes, $content))
			{
				return 'MARKETING_CONSENT_VENDOR_RECOMMENDED';
			}

			return 'MARKETING_CONSENT_RECOMMENDED';
		}

		return false;
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

		if (preg_match('/\bsrc\s*=\s*([\'"])(.*?)\1/i', $attributes, $matches))
		{
			$haystacks[] = $matches[2];
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
