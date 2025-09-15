<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\controller;

use phpbb\ads\ad\manager;
use phpbb\ads\controller\increment_controller;
use phpbb\exception\http_exception;
use phpbb\request\request;
use phpbb_database_test_case;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;

class increment_controller_test extends phpbb_database_test_case
{
	/** @var MockObject|manager */
	protected manager|MockObject $manager;

	/** @var MockObject|request */
	protected MockObject|request $request;

	/**
	 * {@inheritDoc}
	 */
	protected static function setup_extensions(): array
	{
		return array('phpbb/ads');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/../fixtures/clicks.xml');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->manager = $this->getMockBuilder(manager::class)
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMockBuilder(request::class)
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * Returns fresh new controller.
	 *
	 * @return	increment_controller	Increment controller
	 */
	public function get_controller(): increment_controller
	{
		return new increment_controller(
			$this->manager,
			$this->request
		);
	}

	/**
	 * Test data for the test_increment_clicks() function
	 *
	 * @return array Array of test data
	 */
	public function increment_clicks_data(): array
	{
		return array(
			array(0, true),
			array(1, false),
			array(1, true),
		);
	}
	/**
	 * Test increment_clicks() method
	 *
	 * @dataProvider increment_clicks_data
	 */
	public function test_increment_clicks($ad_id, $is_ajax)
	{
		$controller = $this->get_controller();

		$this->request->expects($ad_id ? self::once() : self::never())
			->method('is_ajax')
			->willReturn($is_ajax);

		try
		{
			$response = $controller->handle($ad_id, 'clicks');
			self::assertInstanceOf(JsonResponse::class, $response);
		}
		catch (http_exception $exception)
		{
			self::assertEquals(403, $exception->getStatusCode());
			self::assertEquals('NOT_AUTHORISED', $exception->getMessage());
		}
	}


	/**
	 * Test data for the test_increment_clicks() function
	 *
	 * @return array Array of test data
	 */
	public function increment_views_data(): array
	{
		return array(
			array('0', true),
			array('1', false),
			array('1', true),
			array('1-2', true),
		);
	}
	/**
	 * Test increment_views() method
	 *
	 * @dataProvider increment_views_data
	 */
	public function test_increment_views($ad_ids, $is_ajax)
	{
		$controller = $this->get_controller();

		$this->request->expects(!empty($ad_ids) ? self::once() : self::never())
			->method('is_ajax')
			->willReturn($is_ajax);

		$this->manager->expects(($is_ajax && !empty($ad_ids)) ? self::once() : self::never())
			->method('increment_ads_views')
			->with(explode('-', $ad_ids));

		try
		{
			$response = $controller->handle($ad_ids, 'views');

			self::assertInstanceOf(JsonResponse::class, $response);
		}
		catch (http_exception $exception)
		{
			self::assertEquals(403, $exception->getStatusCode());
			self::assertEquals('NOT_AUTHORISED', $exception->getMessage());
		}
	}
}
