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

	public function test_defers_script_tag_when_consent_enabled()
	{
		$raw = htmlspecialchars('<script src="https://ads.example.com/tag.js"></script>', ENT_COMPAT);
		$result = $this->get_manager()->prepare_ad_code($raw, true);
		self::assertStringContainsString('type="text/plain"', $result);
		self::assertStringContainsString('data-consent-category="marketing"', $result);
		self::assertStringContainsString('src="https://ads.example.com/tag.js"', $result);
	}

	public function test_adds_consent_category_to_existing_text_plain_script()
	{
		$raw = htmlspecialchars('<script type="text/plain" src="https://ads.example.com/legacy.js"></script>', ENT_COMPAT);
		$result = $this->get_manager()->prepare_ad_code($raw, true);
		self::assertStringContainsString('type="text/plain"', $result);
		self::assertStringContainsString('data-consent-category="marketing"', $result);
	}

	public function test_does_not_double_wrap_already_tagged_script()
	{
		$raw = htmlspecialchars('<script type="text/plain" data-consent-category="marketing" src="https://ads.example.com/tag.js"></script>', ENT_COMPAT);
		$result = $this->get_manager()->prepare_ad_code($raw, true);
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
