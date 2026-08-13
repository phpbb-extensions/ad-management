<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2022 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests;

use phpbb\ads\ext;
use phpbb\finder\finder;
use phpbb_test_case;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerInterface;
use phpbb\db\migrator;

class ext_test extends phpbb_test_case
{
	public function test_ext()
	{
		/** @var MockObject|ContainerInterface $container */
		$container = $this->getMockBuilder(ContainerInterface::class)
			->disableOriginalConstructor()
			->getMock();

		/** @var MockObject|finder $extension_finder */
		$extension_finder = $this->getMockBuilder(finder::class)
			->disableOriginalConstructor()
			->getMock();

		/** @var MockObject|\phpbb\db\migrator $migrator */
		$migrator = $this->getMockBuilder(migrator::class)
			->disableOriginalConstructor()
			->getMock();

		$ext = new ext(
			$container,
			$extension_finder,
			$migrator,
			'phpbb/ads',
			''
		);

		self::assertTrue($ext->is_enableable());
	}

	/**
	 * @dataProvider notification_lifecycle_data
	 */
	public function test_notification_lifecycle($step, $notification_method)
	{
		$notifications = $this->getMockBuilder('\phpbb\notification\manager')
			->disableOriginalConstructor()
			->getMock();
		$notifications->expects(self::once())
			->method($notification_method)
			->with(\phpbb\ads\ext::NOTIFICATION_TYPE_DISABLED);

		$container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')
			->disableOriginalConstructor()
			->getMock();
		$container->expects(self::once())
			->method('get')
			->with('notification_manager')
			->willReturn($notifications);
		$finder = $this->getMockBuilder(finder::class)->disableOriginalConstructor()->getMock();
		$finder->method('extension_directory')->willReturnSelf();
		$finder->method('find_from_extension')->willReturn(array());
		$finder->method('get_classes_from_files')->willReturn(array());
		$migrator = $this->getMockBuilder('\phpbb\db\migrator')->disableOriginalConstructor()->getMock();
		$migrator->method('get_migrations')->willReturn(array());
		$migrator->method('finished')->willReturn(true);
		$ext = new \phpbb\ads\ext($container, $finder, $migrator, 'phpbb/ads', '');

		self::assertFalse($ext->{$step}(false));
	}

	public static function notification_lifecycle_data()
	{
		return array(
			array('enable_step', 'enable_notifications'),
			array('disable_step', 'disable_notifications'),
			array('purge_step', 'purge_notifications'),
		);
	}
}
