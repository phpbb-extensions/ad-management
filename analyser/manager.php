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

	/**
	 * Construct an ad code analysis manager object
	 *
	 * @param array $tests Ad code analysis tests passed via the service container
	 */
	public function __construct($tests)
	{
		$this->tests = $tests;
	}

	/**
	 * Test the ad code for potential problems.
	 *
	 * @param	string	$ad_code	Advertisement code
	 * @return	array	Analysis results
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

		return $results;
	}
}
