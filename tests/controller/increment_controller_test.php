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
	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\ads\ad\manager */
	protected $manager;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\request\request */
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
	protected function setUp(): void
	{
		parent::setUp();

		global $user;
		$user = new \stdClass();
		$user->data = array('user_form_salt' => 'test-form-salt');

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
			array(0, true, true),
			array(1, false, true),
			array(1, true, false),
			array(1, true, true),
		);
	}
	/**
	 * Test increment_clicks() method
	 *
	 * @dataProvider increment_clicks_data
	 */
	public function test_increment_clicks($ad_id, $is_ajax, $valid_hash)
	{
		$controller = $this->get_controller();

		$this->request->expects($ad_id ? self::once() : self::never())
			->method('is_ajax')
			->willReturn($is_ajax);

		$this->request->expects(($ad_id && $is_ajax) ? self::once() : self::never())
			->method('variable')
			->with('hash', '')
			->willReturn($valid_hash ? generate_link_hash('phpbb_ads_click') : 'invalid');

		$this->manager->expects(($ad_id && $is_ajax && $valid_hash) ? self::once() : self::never())
			->method('increment_ad_clicks')
			->with($ad_id);

		try
		{
			$response = $controller->handle($ad_id, 'clicks');
			self::assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
		}
		catch (\phpbb\exception\http_exception $exception)
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
	public function increment_views_data()
	{
		return array(
			array('0', true, true),
			array('1', false, true),
			array('1', true, false),
			array('1', true, true),
			array('1-2', true, true),
		);
	}
	/**
	 * Test increment_views() method
	 *
	 * @dataProvider increment_views_data
	 */
	public function test_increment_views($ad_ids, $is_ajax, $valid_hash)
	{
		$controller = $this->get_controller();

		$this->request->expects(!empty($ad_ids) ? self::once() : self::never())
			->method('is_ajax')
			->willReturn($is_ajax);

		$this->request->expects(($is_ajax && !empty($ad_ids)) ? self::once() : self::never())
			->method('variable')
			->with('hash', '')
			->willReturn($valid_hash ? generate_link_hash('phpbb_ads_views_' . $ad_ids) : 'invalid');

		$this->manager->expects(($is_ajax && !empty($ad_ids) && $valid_hash) ? self::once() : self::never())
			->method('increment_ads_views')
			->with(explode('-', $ad_ids));

		try
		{
			$response = $controller->handle($ad_ids, 'views');

			self::assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
		}
		catch (\phpbb\exception\http_exception $exception)
		{
			self::assertEquals(403, $exception->getStatusCode());
			self::assertEquals('NOT_AUTHORISED', $exception->getMessage());
		}
	}
}
