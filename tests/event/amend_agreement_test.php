<?php
/**
 *
 * Pages extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\event;

class amend_agreement_test extends main_listener_base
{
	/**
	 * Data for test_append_agreement
	 *
	 * @return array
	 */
	public function append_agreement_data()
	{
		return [
			[true, false, 'PRIVACY', 0], // No agreement
			[true, true, 'TERMS', 0], // Wrong title
			[false, true, 'PRIVACY', 0], // don't display agreement
			[true, true, 'PRIVACY', 1], // Correct conditions
		];
	}

	/**
	 * Test the append_agreement method
	 *
	 * @dataProvider append_agreement_data
	 * @param bool $s_show S_SHOW template variable value
	 * @param mixed $s_agreement S_AGREEMENT template variable value
	 * @param mixed $agreement_title AGREEMENT_TITLE template variable value
	 * @param int $expected_append_calls Expected append_var calls
	 */
	public function test_append_agreement($s_show, $s_agreement, $agreement_title, $expected_append_calls)
	{
		$this->config['phpbb_ads_show_agreement'] = $s_show;
		$this->config['sitename'] = 'Test Forum';
		$this->user->page['page_name'] = 'ucp.php';
		$privacy_policy = '<br><br><h3>Advertising</h3>Privacy policy text';

		$this->template->expects(self::atMost(2))
			->method('retrieve_var')
			->withConsecutive(['S_AGREEMENT'], ['AGREEMENT_TITLE'])
			->willReturnOnConsecutiveCalls($s_agreement, $this->language->lang($agreement_title));

		$this->template->expects($expected_append_calls ? self::once() : self::never())
			->method('set_filenames')
			->with([
				'phpbb_ads_privacy_policy' => '@phpbb_ads/privacy_policy.html',
			]);

		$this->template->expects($expected_append_calls ? self::once() : self::never())
			->method('assign_display')
			->with('phpbb_ads_privacy_policy')
			->willReturn($privacy_policy);

		$this->template->expects($expected_append_calls ? self::once() : self::never())
			->method('append_var')
			->with('AGREEMENT_TEXT', $privacy_policy);

		$listener = $this->get_listener();
		$listener->append_agreement();
	}

	public function test_privacy_policy_language_has_only_break_markup()
	{
		global $phpbb_root_path, $phpEx;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang_loader->set_extension_manager(new \phpbb_mock_extension_manager($phpbb_root_path));
		$language = new \phpbb\language\language($lang_loader);
		$language->add_lang('ucp', 'phpbb/ads');
		$privacy_policy = $language->lang('PHPBB_ADS_PRIVACY_POLICY_TEXT', 'Test Forum');

		self::assertStringNotContainsString('<', $language->lang('PHPBB_ADS_PRIVACY_POLICY_TITLE'));
		self::assertStringContainsString('<br><br>', $privacy_policy);
		self::assertStringNotContainsString('<', str_replace('<br>', '', $privacy_policy));
	}
}
