<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\analyser;

class run_test extends analyser_base
{
	/**
	 * Test data provider for test_run()
	 *
	 * @return array Array of test data
	 */
	public function run_data()
	{
		return array(
			array('&lt;script async&gt;alert()&lt;/script&gt;', false, array(
				array(
					'severity'	=> 'warning',
					'lang_key'	=> 'ALERT_USAGE',
				),
			)),
			array('&lt;script async&gt;alert ()&lt;/script&gt;', false, array(
				array(
					'severity'	=> 'warning',
					'lang_key'	=> 'ALERT_USAGE',
				),
			)),
			array('&lt;script async&gt;window.location.href = "new url"&lt;/script&gt;', false, array(
				array(
					'severity'	=> 'warning',
					'lang_key'	=> 'LOCATION_CHANGE',
				),
			)),
			array('&lt;script async&gt;window.location.href= "new url"&lt;/script&gt;', false, array(
				array(
					'severity'	=> 'warning',
					'lang_key'	=> 'LOCATION_CHANGE',
				),
			)),
			array('&lt;script&gt;&lt;/script&gt;', false, array()),
			array('&lt;script src="script src"&gt;&lt;/script&gt;', false, array(
				array(
					'severity'	=> 'notice',
					'lang_key'	=> 'SCRIPT_WITHOUT_ASYNC',
				),
			)),
			array('&lt;script src="script src"&gt;&lt;/script&gt;&lt;script src="another script src"&gt;&lt;/script&gt;', false, array(
				array(
					'severity'	=> 'notice',
					'lang_key'	=> 'SCRIPT_WITHOUT_ASYNC',
				),
			)),
			array('&lt;script async src="script src"&gt;&lt;/script&gt;&lt;script src="another script src"&gt;&lt;/script&gt;', false, array(
				array(
					'severity'	=> 'notice',
					'lang_key'	=> 'SCRIPT_WITHOUT_ASYNC',
				),
			)),
			array('&lt;script src="script src"&gt;&lt;/script&gt;&lt;script async src="another script src"&gt;&lt;/script&gt;', false, array(
				array(
					'severity'	=> 'notice',
					'lang_key'	=> 'SCRIPT_WITHOUT_ASYNC',
				),
			)),
			array('&lt;script async src="http://some.url"&gt;&lt;/script&gt;', false, array()),
			array('&lt;script async src="https://some.url"&gt;&lt;/script&gt;', true, array()),
			array('&lt;script async src="http://some.url"&gt;&lt;/script&gt;', true, array(
				array(
					'severity'	=> 'warning',
					'lang_key'	=> 'UNSECURE_CONNECTION',
				),
			)),
			array('&lt;script src="http://some.url"&gt;&lt;/script&gt;&lt;script&gt;alert("e");window.location.href="new url"&lt;/script&gt;', true, array(
				array(
					'severity'	=> 'warning',
					'lang_key'	=> 'ALERT_USAGE',
				),
				array(
					'severity'	=> 'warning',
					'lang_key'	=> 'LOCATION_CHANGE',
				),
				array(
					'severity'	=> 'notice',
					'lang_key'	=> 'SCRIPT_WITHOUT_ASYNC',
				),
				array(
					'severity'	=> 'warning',
					'lang_key'	=> 'UNSECURE_CONNECTION',
				),
			)),
		);
	}

	/**
	 * Test run() method
	 *
	 * @dataProvider run_data
	 */
	public function test_run($ad_code, $is_https, $expected)
	{
		$manager = $this->get_manager();

		$this->request
			->method('server')
			->with('HTTPS', false)
			->willReturn($is_https);

		if (count($expected))
		{
			$i = 0;
			foreach ($expected as $message)
			{
				$this->template->expects($this->at($i))
					->method('assign_block_vars')
					->with('analyser_results_' . $message['severity'], array(
						'MESSAGE'	=> $this->lang->lang($message['lang_key']),
					));

				$i++;
			}
		}
		else
		{
			$this->template->expects($this->never())
				->method('assign_block_vars');
		}

		$manager->run($ad_code);
	}
}
