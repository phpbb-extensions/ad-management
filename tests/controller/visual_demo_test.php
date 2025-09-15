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

use phpbb\ads\controller\visual_demo_controller;
use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\exception\http_exception;
use phpbb\request\request;
use phpbb\user;
use phpbb_mock_event_dispatcher;
use phpbb_test_case;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use phpbb\path_helper;
use Symfony\Component\HttpFoundation\JsonResponse;

class visual_demo_test extends phpbb_test_case
{
	protected auth|MockObject $auth;
	protected config $config;
	protected MockObject|request $request;
	protected user|MockObject $user;
	protected string $phpbb_root_path;
	protected string $phpEx;

	protected function setUp(): void
	{
		parent::setUp();

		global $config, $phpbb_dispatcher, $phpbb_path_helper, $phpbb_root_path, $phpEx, $user;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$phpbb_path_helper = $this->getMockBuilder(path_helper::class)
			->disableOriginalConstructor()
			->getMock();

		$phpbb_path_helper->method('update_web_root_path')
			->willReturnArgument(0);

		$this->auth = $this->getMockBuilder(auth::class)->getMock();

		$config = $this->config = new config(array(
			'cookie_name' => 'test',
		));

		$this->request = $this->getMockBuilder(request::class)
			->disableOriginalConstructor()
			->getMock();
		$this->request->method('variable')
			->willReturnArgument(0);

		$user = $this->user = $this->getMockBuilder(user::class)
			->disableOriginalConstructor()
			->getMock();
		$this->user->data['session_page'] = "index.$phpEx";
		$this->user->page['page_dir'] = '';

		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
	}

	/**
	* Create our controller
	*/
	protected function get_controller(): visual_demo_controller
	{
		return new visual_demo_controller(
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
	public function controller_data(): array
	{
		return array(
			array(
				'enable',
				false,
				200,
				0,
			),
			array(
				'enable',
				true,
				200,
				0,
			),
			array(
				'disable',
				true,
				200,
				1,
			),
			array(
				'disable',
				false,
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
	public function test_controller($action, $is_ajax, $status_code, $cookie_time)
	{
		// User is an admin
		$this->auth->expects(self::once())
			->method('acl_get')
			->with($this->stringContains('a_'), $this->anything())
			->willReturn(true);

		$this->request->expects(self::once())
			->method('is_ajax')
			->willReturn($is_ajax);

		$this->user->expects(self::once())
			->method('set_cookie')
			->with('phpbb_ads_visual_demo', $this->anything(), $cookie_time);

		$controller = $this->get_controller();

		// If a non-ajax redirect is encountered, in testing it will trigger_error
		if (!$is_ajax)
		{
			$this->setExpectedTriggerError(E_USER_DEPRECATED);
			$controller->handle($action);
			return; // Skip response assertions since redirect calls exit
		}

		$response = $controller->handle($action);
		self::assertInstanceOf(JsonResponse::class, $response);
		self::assertEquals($status_code, $response->getStatusCode());
	}

	/**
	 * Test data for the test_controller_fails test
	 *
	 * @return array Test data
	 */
	public function controller_fails_data(): array
	{
		return array(
			array(
				'enable',
				403,
				'NO_AUTH_OPERATION',
			),
			array(
				'disable',
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
	public function test_controller_fails($action, $status_code, $message)
	{
		// User is not an admin
		$this->auth->expects(self::once())
			->method('acl_get')
			->with($this->stringContains('a_'), $this->anything())
			->willReturn(false);

		$controller = $this->get_controller();

		try
		{
			$controller->handle($action);
			self::fail('The expected \phpbb\exception\http_exception was not thrown');
		}
		catch (http_exception $exception)
		{
			self::assertEquals($status_code, $exception->getStatusCode());
			self::assertEquals($message, $exception->getMessage());
		}
	}
}
