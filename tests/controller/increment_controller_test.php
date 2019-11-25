<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\controller;

class increment_controller_test extends \phpbb_database_test_case
{
	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\ads\ad\manager */
	protected $manager;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\request\request */
	protected $request;

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
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/../fixtures/clicks.xml');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setUp(): void
	{
		parent::setUp();

		$this->manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * Returns fresh new controller.
	 *
	 * @return	\phpbb\ads\controller\increment_controller	Increment controller
	 */
	public function get_controller()
	{
		$controller = new \phpbb\ads\controller\increment_controller(
			$this->manager,
			$this->request
		);

		return $controller;
	}

	/**
	 * Test data for the test_increment_clicks() function
	 *
	 * @return array Array of test data
	 */
	public function increment_clicks_data()
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

		$this->request->expects($ad_id ? $this->once() : $this->never())
			->method('is_ajax')
			->willReturn($is_ajax);

		try
		{
			$response = $controller->handle($ad_id, 'clicks');
			$this->assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
		}
		catch (\phpbb\exception\http_exception $exception)
		{
			$this->assertEquals(403, $exception->getStatusCode());
			$this->assertEquals('NOT_AUTHORISED', $exception->getMessage());
		}
	}


	/**
	 * Test data for the test_increment_clicks() function
	 *
	 * @return array Array of test data
	 */
	public function increment_views_data()
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

		$this->request->expects(!empty($ad_ids) ? $this->once() : $this->never())
			->method('is_ajax')
			->willReturn($is_ajax);

		$this->manager->expects(($is_ajax && !empty($ad_ids)) ? $this->once() : $this->never())
			->method('increment_ads_views')
			->with(explode('-', $ad_ids));

		try
		{
			$response = $controller->handle($ad_ids, 'views');

			$this->assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
		}
		catch (\phpbb\exception\http_exception $exception)
		{
			$this->assertEquals(403, $exception->getStatusCode());
			$this->assertEquals('NOT_AUTHORISED', $exception->getMessage());
		}
	}
}
