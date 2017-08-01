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

class view_controller_test extends \phpbb_database_test_case
{
	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\ads\ad\manager */
	protected $manager;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\request\request */
	protected $request;

	/**
	 * {@inheritDoc}
	 */
	static protected function setup_extensions()
	{
		return array('phpbb/ads');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/../fixtures/views.xml');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setUp()
	{
		parent::setUp();

		$this->manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMock('\phpbb\request\request');
	}

	/**
	 * Returns fresh new controller.
	 *
	 * @return	\phpbb\ads\controller\view_controller	Click controller
	 */
	public function get_controller()
	{
		$controller = new \phpbb\ads\controller\view_controller(
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

		$this->request->expects($this->once())
			->method('is_ajax')
			->willReturn($is_ajax);

		$this->manager->expects(($is_ajax && $ad_ids != '') ? $this->once() : $this->never())
			->method('increment_ads_views')
			->with(explode('-', $ad_ids));

		try
		{
			$response = $controller->increment_views($ad_ids);

			$this->assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
		}
		catch (\phpbb\exception\http_exception $exception)
		{
			$this->assertEquals(403, $exception->getStatusCode());
			$this->assertEquals('NOT_AUTHORISED', $exception->getMessage());
		}
	}
}
