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
			'warns on alert call' => array('&lt;script async&gt;alert()&lt;/script&gt;', false, array(), array(
				array(
					'severity'	=> 'warning',
					'lang_key'	=> 'ALERT_USAGE',
				),
			)),
			'warns on spaced alert call' => array('&lt;script async&gt;alert ()&lt;/script&gt;', false, array(), array(
				array(
					'severity'	=> 'warning',
					'lang_key'	=> 'ALERT_USAGE',
				),
			)),
			'warns on location href assignment' => array('&lt;script async&gt;window.location.href = "new url"&lt;/script&gt;', false, array(), array(
				array(
					'severity'	=> 'warning',
					'lang_key'	=> 'LOCATION_CHANGE',
				),
			)),
			'warns on compact location href assignment' => array('&lt;script async&gt;window.location.href= "new url"&lt;/script&gt;', false, array(), array(
				array(
					'severity'	=> 'warning',
					'lang_key'	=> 'LOCATION_CHANGE',
				),
			)),
			'allows empty script without src' => array('&lt;script&gt;&lt;/script&gt;', false, array(), array()),
			'notices script without async' => array('&lt;script src="script src"&gt;&lt;/script&gt;', false, array(), array(
				array(
					'severity'	=> 'notice',
					'lang_key'	=> 'SCRIPT_WITHOUT_ASYNC',
				),
			)),
			'notices first of multiple scripts without async' => array('&lt;script src="script src"&gt;&lt;/script&gt;&lt;script src="another script src"&gt;&lt;/script&gt;', false, array(), array(
				array(
					'severity'	=> 'notice',
					'lang_key'	=> 'SCRIPT_WITHOUT_ASYNC',
				),
			)),
			'notices second script without async' => array('&lt;script async src="script src"&gt;&lt;/script&gt;&lt;script src="another script src"&gt;&lt;/script&gt;', false, array(), array(
				array(
					'severity'	=> 'notice',
					'lang_key'	=> 'SCRIPT_WITHOUT_ASYNC',
				),
			)),
			'notices first script without async before async script' => array('&lt;script src="script src"&gt;&lt;/script&gt;&lt;script async src="another script src"&gt;&lt;/script&gt;', false, array(), array(
				array(
					'severity'	=> 'notice',
					'lang_key'	=> 'SCRIPT_WITHOUT_ASYNC',
				),
			)),
			'allows http script on http page' => array('&lt;script async src="http://some.url"&gt;&lt;/script&gt;', false, array(), array()),
			'allows https script on https page' => array('&lt;script async src="https://some.url"&gt;&lt;/script&gt;', true, array(), array()),
			'warns on http script on https page' => array('&lt;script async src="http://some.url"&gt;&lt;/script&gt;', true, array(), array(
				array(
					'severity'	=> 'warning',
					'lang_key'	=> 'UNSECURE_CONNECTION',
				),
			)),
			'collects multiple analyser warnings' => array('&lt;script src="http://some.url"&gt;&lt;/script&gt;&lt;script&gt;alert("e");window.location.href="new url"&lt;/script&gt;', true, array(), array(
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
			'notices iframe usage' => array('&lt;iframe src=&quot;https://some.url&quot; width=&quot;640&quot; height=&quot;360&quot; allowfullscreen&gt;&lt;/iframe&gt;', false, array(), array(
				array(
					'severity'	=> 'notice',
					'lang_key'	=> 'IFRAME_USAGE',
				),
			)),
			'allows consent-aware iframe placeholder' => array('&lt;iframe data-consent-src=&quot;https://some.url&quot; width=&quot;640&quot; height=&quot;360&quot; allowfullscreen&gt;&lt;/iframe&gt;', false, array(), array()),
			'recommends marketing consent for generic ad script' => array('<script src="https://ads.example.com/tag.js" async></script>', false, array(
				'consentmanager_marketing_enabled' => 1,
			), array(
				array(
					'severity'	=> 'notice',
					'lang_key'	=> 'MARKETING_CONSENT_RECOMMENDED',
				),
			)),
			'recommends marketing consent for inline cookie script' => array('<script>document.cookie = "ad=1";</script>', false, array(
				'consentmanager_marketing_enabled' => 1,
			), array(
				array(
					'severity'	=> 'notice',
					'lang_key'	=> 'MARKETING_CONSENT_RECOMMENDED',
				),
			)),
			'recommends marketing consent for known non-Google vendor script' => array('<script src="https://cdn.taboola.com/tag.js" async></script>', false, array(
				'consentmanager_marketing_enabled' => 1,
			), array(
				array(
					'severity'	=> 'notice',
					'lang_key'	=> 'MARKETING_CONSENT_VENDOR_RECOMMENDED',
				),
			)),
			'allows AdSense loader under Google Consent Mode' => array('<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-123"></script>', false, array(
				'consentmanager_marketing_enabled' => 1,
			), array()),
			'allows full AdSense snippet under Google Consent Mode' => array('<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-123" crossorigin="anonymous"></script><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-123" data-ad-slot="456" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>', false, array(
				'consentmanager_marketing_enabled' => 1,
			), array()),
			'allows GPT loader under Google Consent Mode' => array('<script async src="//securepubads.g.doubleclick.net/tag/js/gpt.js"></script>', false, array(
				'consentmanager_marketing_enabled' => 1,
			), array()),
			'allows non-executable json script' => array('<script type="application/ld+json">{"@context":"https://schema.org"}</script>', false, array(
				'consentmanager_marketing_enabled' => 1,
			), array()),
			'allows Google ad iframe because marketing consent analyser only handles scripts' => array('<iframe src="https://googleads.g.doubleclick.net/pagead/ads"></iframe>', false, array(
				'consentmanager_marketing_enabled' => 1,
			), array()),
			'recommends marketing consent regardless of ad consent form value' => array('<script src="https://ads.example.com/tag.js" async></script>', false, array(
				'consentmanager_marketing_enabled' => 1,
			), array(
				array(
					'severity'	=> 'notice',
					'lang_key'	=> 'MARKETING_CONSENT_RECOMMENDED',
				),
			)),
			'allows generic ad script when Consent Manager marketing category is disabled' => array('<script src="https://ads.example.com/tag.js" async></script>', false, array(
				'consentmanager_marketing_enabled' => 0,
			), array()),
			'allows already consent-tagged script' => array('<script type="text/plain" data-consent-category="marketing" src="https://ads.example.com/tag.js"></script>', false, array(
				'consentmanager_marketing_enabled' => 1,
			), array()),
		);
	}

	/**
	 * Test run() method
	 *
	 * @dataProvider run_data
	 */
	public function test_run($ad_code, $is_https, $config, $expected)
	{
		$manager = $this->get_manager();
		$this->config['consentmanager_marketing_enabled'] = $config['consentmanager_marketing_enabled'] ?? 0;

		$this->request
			->method('server')
			->with('HTTPS', false)
			->willReturn($is_https);

		if (count($expected))
		{
			$analyser_results = [];
			foreach ($expected as $message)
			{
				$analyser_results = array_merge($analyser_results, [['analyser_results_' . $message['severity'], [
					'MESSAGE'	=> $this->lang->lang($message['lang_key'])]]
				]);
			}

			$this->template->expects(self::exactly(count($expected)))
				->method('assign_block_vars')
				->withConsecutive(...$analyser_results);
		}
		else
		{
			$this->template->expects(self::never())
				->method('assign_block_vars');
		}

		$manager->run($ad_code);
	}
}
