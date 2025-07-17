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
}
