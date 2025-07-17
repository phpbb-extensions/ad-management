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

use phpbb\ads\analyser\manager;
use phpbb\language\language;
use phpbb\language\language_file_loader;
use phpbb\request\request;
use phpbb\template\template;
use phpbb_test_case;
use PHPUnit\Framework\MockObject\MockObject;

class analyser_base extends phpbb_test_case
{
	/** @var array Ad code analysis tests */
	protected array $tests;

	/** @var MockObject|request */
	protected MockObject|request $request;

	/** @var MockObject|template */
	protected template|MockObject $template;

	/** @var language */
	protected language $lang;

	protected static function setup_extensions(): array
	{
		return array('phpbb/ads');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$lang_loader = new language_file_loader($phpbb_root_path, $phpEx);

		$this->request = $this->getMockBuilder(request::class)
			->disableOriginalConstructor()
			->getMock();
		$this->template = $this->getMockBuilder(template::class)
			->disableOriginalConstructor()
			->getMock();
		$this->lang = new language($lang_loader);

		// Tests
		$tests = array(
			'alert',
			'location_href',
			'script_without_async',
			'untrusted_connection',
		);
		$analyser_tests = array();
		foreach ($tests as $test)
		{
			$class = "\\phpbb\\ads\\analyser\\test\\$test";
			if ($test === 'untrusted_connection')
			{
				$analyser_tests['phpbb.ads.analyser.test.' . $test] = new $class($this->request);
			}
			else
			{
				$analyser_tests['phpbb.ads.analyser.test.' . $test] = new $class();
			}
		}

		$this->tests = $analyser_tests;
	}

	/**
	 * Returns fresh new ad code analyser manager.
	 *
	 * @return    manager    Ad code analyser manager
	 */
	public function get_manager(): manager
	{
		return new manager(
			$this->tests,
			$this->template,
			$this->lang
		);
	}
}
