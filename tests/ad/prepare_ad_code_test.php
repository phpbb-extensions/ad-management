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

	public function test_non_script_html_is_preserved()
	{
		$raw = htmlspecialchars('<div class="ad-slot">Ad</div><iframe src="https://ads.example.com/frame"></iframe>', ENT_COMPAT);
		$result = $this->get_manager()->prepare_ad_code($raw, true);
		self::assertStringContainsString('<iframe src="https://ads.example.com/frame"></iframe>', $result);
		self::assertStringNotContainsString('type="text/plain"', $result);
	}
}
