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
	public static function append_agreement_data()
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

		$this->template->expects(self::atMost(2))
			->method('retrieve_var')
			->willReturnMap([
				['S_AGREEMENT', $s_agreement],
				['AGREEMENT_TITLE', $this->language->lang($agreement_title)]
			]);

		if ($expected_append_calls > 0)
		{
			$this->template->expects(self::once())
				->method('append_var')
				->with('AGREEMENT_TEXT', $this->language->lang('PHPBB_ADS_PRIVACY_POLICY', 'Test Forum'));
		}
		else
		{
			$this->template->expects(self::never())
				->method('append_var');
		}

		$listener = $this->get_listener();
		$listener->append_agreement();
	}
}
