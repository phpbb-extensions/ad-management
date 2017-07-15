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

class click_controller_test extends \phpbb_database_test_case
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
		return $this->createXMLDataSet(__DIR__ . '/../fixtures/clicks.xml');
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
	 * @return	\phpbb\ads\controller\click_controller	Click controller
	 */
	public function get_controller()
	{
		$controller = new \phpbb\ads\controller\click_controller(
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
	 * Test action_delete() method
	 *
	 * @dataProvider increment_clicks_data
	 */
	public function test_increment_clicks($ad_id, $is_ajax)
	{
		$controller = $this->get_controller();
		$db = $this->new_dbal();

		$this->request->expects($this->once())
			->method('is_ajax')
			->willReturn($is_ajax);

		try
		{
			$response = $controller->increment_clicks($ad_id);
			$this->assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
		}
		catch (\phpbb\exception\http_exception $exception)
		{
			$this->assertEquals(403, $exception->getStatusCode());
			$this->assertEquals('NOT_AUTHORISED', $exception->getMessage());
		}
	}
}
