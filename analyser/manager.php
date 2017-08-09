<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\analyser;

class manager
{
	/** @var array Ad code analysis tests */
	protected $tests;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $lang;

	/**
	 * Construct an ad code analysis manager object
	 *
	 * @param	array						$tests		Ad code analysis tests passed via the service container
	 * @param	\phpbb\request\request		$request	Request object
	 * @param	\phpbb\template\template	$template	Template object
	 * @param	\phpbb\language\language	$lang		Language object
	 */
	public function __construct($tests, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\language\language $lang)
	{
		$this->tests = $tests;
		$this->request = $request;
		$this->template = $template;
		$this->lang = $lang;
	}

	/**
	 * Test the ad code for potential problems.
	 *
	 * @param	string	$ad_code	Advertisement code
	 */
	public function run($ad_code)
	{
		$results = array();
		foreach ($this->tests as $test)
		{
			$result = $test->run($ad_code);
			if ($result !== false)
			{
				$results[] = $result;
			}
		}

		$this->assign_template_vars($results);
	}

	/**
	 * Assign analyser results to template variables.
	 *
	 * @param	array	$results	Analyser results
	 */
	protected function assign_template_vars($results)
	{
		foreach ($results as $result)
		{
			$this->template->assign_block_vars('analyser_results_' . $result['severity'], array(
				'MESSAGE'	=> $this->lang->lang($result['message']),
			));
		}

		$this->template->assign_var('CODE_ANALYSED', true);
	}
}
