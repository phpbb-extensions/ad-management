<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\ad;

class prepare_ad_code_test extends ad_base
{
	public function test_consent_category_constant()
	{
		self::assertSame('marketing', \phpbb\ads\ad\manager::CONSENT_CATEGORY);
	}

	public function test_returns_decoded_code_when_consent_disabled()
	{
		$raw = htmlspecialchars('<script src="https://ads.example.com/tag.js"></script>', ENT_COMPAT);
		$result = $this->get_manager()->prepare_ad_code($raw, false);
		self::assertSame('<script src="https://ads.example.com/tag.js"></script>', $result);
		self::assertStringNotContainsString('type="text/plain"', $result);
	}

	public function test_returns_empty_string_unchanged()
	{
		self::assertSame('', $this->get_manager()->prepare_ad_code('', true));
	}

	public function executable_script_type_data()
	{
		return [
			'normal script' => [
				'<script src="https://ads.example.com/tag.js"></script>',
				'<script src="https://ads.example.com/tag.js" type="text/plain" data-consent-category="marketing"></script>',
			],
			'empty type' => [
				'<script type="" src="https://ads.example.com/legacy.js"></script>',
				'<script type="text/plain" src="https://ads.example.com/legacy.js" data-consent-category="marketing"></script>',
			],
			'text/plain type' => [
				'<script type="text/plain" src="https://ads.example.com/legacy.js"></script>',
				'<script type="text/plain" src="https://ads.example.com/legacy.js" data-consent-category="marketing"></script>',
			],
			'module type' => [
				'<script type="module" src="https://ads.example.com/legacy.js"></script>',
				'<script type="text/plain" src="https://ads.example.com/legacy.js" data-consent-category="marketing"></script>',
			],
			'javascript type with charset' => [
				'<script type="application/javascript; charset=utf-8" src="https://ads.example.com/legacy.js"></script>',
				'<script type="text/plain" src="https://ads.example.com/legacy.js" data-consent-category="marketing"></script>',
			],
			'ecmascript type' => [
				'<script type="text/ecmascript" src="https://ads.example.com/legacy.js"></script>',
				'<script type="text/plain" src="https://ads.example.com/legacy.js" data-consent-category="marketing"></script>',
			],
		];
	}

	/**
	 * @dataProvider executable_script_type_data
	 */
	public function test_defers_executable_script_types($input, $expected)
	{
		$raw = htmlspecialchars($input, ENT_COMPAT);
		$result = $this->get_manager()->prepare_ad_code($raw, true);
		self::assertSame($expected, $result);
	}

	public function test_preserves_non_executable_script_type()
	{
		$raw = htmlspecialchars('<script type="application/json">{"slot":"ad"}</script>', ENT_COMPAT);
		$result = $this->get_manager()->prepare_ad_code($raw, true);
		self::assertSame('<script type="application/json">{"slot":"ad"}</script>', $result);
	}

	public function test_does_not_double_wrap_already_tagged_script()
	{
		$script = '<script type="text/plain" data-consent-category="marketing" src="https://ads.example.com/tag.js"></script>';
		$raw = htmlspecialchars($script, ENT_COMPAT);
		$result = $this->get_manager()->prepare_ad_code($raw, true);
		self::assertSame($script, $result);
		self::assertSame(1, substr_count($result, 'data-consent-category='));
	}

	public function google_consent_aware_script_data()
	{
		return [
			'adsense loader' => [
				'<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-123"></script>',
			],
			'gpt loader' => [
				'<script async src="//securepubads.g.doubleclick.net/tag/js/gpt.js"></script>',
			],
			'gtag loader' => [
				'<script async src="https://www.googletagmanager.com/gtag/js?id=AW-123"></script>',
			],
			'gtm loader' => [
				'<script async src="https://www.googletagmanager.com/gtm.js?id=GTM-ABC123"></script>',
			],
		];
	}

	/**
	 * @dataProvider google_consent_aware_script_data
	 */
	public function test_does_not_defer_google_consent_aware_loaders($script)
	{
		$raw = htmlspecialchars($script, ENT_COMPAT);
		$result = $this->get_manager()->prepare_ad_code($raw, true);
		self::assertSame($script, $result);
	}

	public function test_does_not_defer_adsense_inline_script_when_adsense_loader_is_present()
	{
		$script = '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-123"></script><ins class="adsbygoogle"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
		$raw = htmlspecialchars($script, ENT_COMPAT);
		$result = $this->get_manager()->prepare_ad_code($raw, true);
		self::assertSame($script, $result);
	}

	public function test_does_not_defer_gpt_inline_script_when_gpt_loader_is_present()
	{
		$script = '<script async src="//securepubads.g.doubleclick.net/tag/js/gpt.js"></script><script>window.googletag = window.googletag || {cmd: []}; googletag.cmd.push(function() {});</script>';
		$raw = htmlspecialchars($script, ENT_COMPAT);
		$result = $this->get_manager()->prepare_ad_code($raw, true);
		self::assertSame($script, $result);
	}

	public function test_defers_google_named_inline_script_without_google_loader()
	{
		$raw = htmlspecialchars('<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>', ENT_COMPAT);
		$result = $this->get_manager()->prepare_ad_code($raw, true);
		self::assertSame('<script type="text/plain" data-consent-category="marketing">(adsbygoogle = window.adsbygoogle || []).push({});</script>', $result);
	}

	public function test_google_consent_aware_source_lookup_returns_empty_without_script_tags()
	{
		self::assertSame(array(), \phpbb\ads\ad\manager::get_google_consent_aware_script_sources('<div class="ad-slot">No scripts</div>'));
	}

	public function test_non_script_html_is_preserved()
	{
		$raw = htmlspecialchars('<div class="ad-slot">Ad</div><iframe src="https://ads.example.com/frame"></iframe>', ENT_COMPAT);
		$result = $this->get_manager()->prepare_ad_code($raw, true);
		self::assertStringContainsString('<iframe src="https://ads.example.com/frame"></iframe>', $result);
		self::assertStringNotContainsString('type="text/plain"', $result);
	}
}
