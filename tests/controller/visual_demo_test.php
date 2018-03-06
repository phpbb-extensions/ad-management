<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\controller;

class visual_demo_test extends \phpbb_test_case
{
	/** @var \phpbb\auth\auth|\PHPUnit_Framework_MockObject_MockObject */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request|\PHPUnit_Framework_MockObject_MockObject */
	protected $request;

	/** @var \phpbb\user|\PHPUnit_Framework_MockObject_MockObject */
	protected $user;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $phpEx;

	public function setUp()
	{
		parent::setUp();

		global $phpbb_dispatcher, $phpbb_path_helper, $phpbb_root_path, $phpEx, $user;

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
		$phpbb_path_helper = $this->getMockBuilder('\phpbb\path_helper')
			->disableOriginalConstructor()
			->getMock();

		$this->auth = $this->getMockBuilder('\phpbb\auth\auth')->getMock();
		$this->config = new \phpbb\config\config(array(
			'cookie_name' => 'test',
		));
		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$user = $this->user = $this->getMockBuilder('\phpbb\user')
			->disableOriginalConstructor()
			->getMock();
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
	}

	/**
	* Create our controller
	*/
	protected function get_controller()
	{
		return new \phpbb\ads\controller\visual_demo_controller(
			$this->auth,
			$this->config,
			$this->request,
			$this->user,
			$this->phpbb_root_path,
			$this->phpEx
		);
	}

	/**
	 * Test data for the test_controller test
	 *
	 * @return array Test data
	 */
	public function controller_data()
	{
		return array(
			array(
				'enable',
				true,
				true, // use ajax to bypass redirects in testing
				200,
				0,
			),
			array(
				'disable',
				true,
				true, // use ajax to bypass redirects in testing
				200,
				1,
			),
		);
	}

	/**
	 * Test the controller response under normal conditions
	 *
	 * @dataProvider controller_data
	 */
	public function test_controller($action, $is_admin, $is_ajax, $status_code, $cookie_time)
	{
		$this->auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('a_'), $this->anything())
			->will($this->returnValue($is_admin));

		$this->request->expects($this->any())
			->method('is_ajax')
			->will($this->returnValue($is_ajax));

		$this->request->expects($this->any())
			->method('is_set')
			->with($this->config['cookie_name'] . '_phpbb_ads_visual_demo')
			->will($this->returnValue(true));

		$this->user->expects($this->once())
			->method('set_cookie')
			->with('phpbb_ads_visual_demo', $this->anything(), $cookie_time);

		$controller = $this->get_controller();

		$response = $controller->handle($action);
		$this->assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
		$this->assertEquals($status_code, $response->getStatusCode());
	}

	/**
	 * Test data for the test_controller_fails test
	 *
	 * @return array Test data
	 */
	public function controller_fails_data()
	{
		return array(
			array(
				'enable',
				false, // is admin
				403,
				'NO_AUTH_OPERATION',
			),
			array(
				'disable',
				false, // is admin
				403,
				'NO_AUTH_OPERATION',
			),
		);
	}

	/**
	 * Test the controller throws exceptions under failing conditions
	 *
	 * @dataProvider controller_fails_data
	 */
	public function test_controller_fails($action, $is_admin, $status_code, $message)
	{
		$this->auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('a_'), $this->anything())
			->will($this->returnValue($is_admin));

		$controller = $this->get_controller();

		try
		{
			$controller->handle($action);
			$this->fail('The expected \phpbb\exception\http_exception was not thrown');
		}
		catch (\phpbb\exception\http_exception $exception)
		{
			$this->assertEquals($status_code, $exception->getStatusCode());
			$this->assertEquals($message, $exception->getMessage());
		}
	}
}
