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

class ext_test extends \phpbb_test_case
{
	public function test_ext()
	{
		/** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface */
		$container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')
			->disableOriginalConstructor()
			->getMock();

		/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\finder */
		$extension_finder = $this->getMockBuilder('\phpbb\finder')
			->disableOriginalConstructor()
			->getMock();

		/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\db\migrator */
		$migrator = $this->getMockBuilder('\phpbb\db\migrator')
			->disableOriginalConstructor()
			->getMock();

		$ext = new \phpbb\ads\ext(
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
		$finder = $this->getMockBuilder('\phpbb\finder')->disableOriginalConstructor()->getMock();
		$finder->method('extension_directory')->willReturnSelf();
		$finder->method('find_from_extension')->willReturn(array());
		$finder->method('get_classes_from_files')->willReturn(array());
		$migrator = $this->getMockBuilder('\phpbb\db\migrator')->disableOriginalConstructor()->getMock();
		$migrator->method('get_migrations')->willReturn(array());
		$migrator->method('finished')->willReturn(true);
		$ext = new \phpbb\ads\ext($container, $finder, $migrator, 'phpbb/ads', '');

		self::assertFalse($ext->{$step}(false));
	}

	public function notification_lifecycle_data()
	{
		return array(
			array('enable_step', 'enable_notifications'),
			array('disable_step', 'disable_notifications'),
			array('purge_step', 'purge_notifications'),
		);
	}
}
