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

class analyser_base extends \phpbb_test_case
{
	/** @var array Ad code analysis tests */
	protected $tests;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\request\request */
	protected $request;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $lang;

	/**
	 * {@inheritDoc}
	 */
	protected static function setup_extensions()
	{
		return array('phpbb/ads');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);

		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->disableOriginalConstructor()
			->getMock();
		$this->lang = new \phpbb\language\language($lang_loader);

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
	 * @return    \phpbb\ads\analyser\manager    Ad code analyser manager
	 */
	public function get_manager()
	{
		return new \phpbb\ads\analyser\manager(
			$this->tests,
			$this->request,
			$this->template,
			$this->lang
		);
	}
}
