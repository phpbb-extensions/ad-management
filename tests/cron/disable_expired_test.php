<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\cron;

class disable_expired_test extends \phpbb_test_case
{
	public function test_run()
	{
		$config = new \phpbb\config\config(array('phpbb_ads_expiration_last_gc' => 0));
		$manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->getMock();
		$manager->expects(self::once())->method('disable_expired_ads');
		$task = new \phpbb\ads\cron\disable_expired($config, $manager);

		$task->run();

		self::assertGreaterThan(0, $config['phpbb_ads_expiration_last_gc']);
	}

	/**
	 * @dataProvider should_run_data
	 */
	public function test_should_run($last_run, $expected)
	{
		$config = new \phpbb\config\config(array('phpbb_ads_expiration_last_gc' => $last_run));
		$manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->getMock();
		$task = new \phpbb\ads\cron\disable_expired($config, $manager);

		self::assertSame($expected, $task->should_run());
	}

	public function should_run_data()
	{
		return array(
			array(0, true),
			array(strtotime('2 hours ago'), true),
			array(time(), false),
		);
	}
}
