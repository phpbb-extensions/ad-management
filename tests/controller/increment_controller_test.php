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
		$user = $this->getMockBuilder('\phpbb\user')->disableOriginalConstructor()->getMock();
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
		return new \phpbb\ads\controller\increment_controller(
			$this->manager,
			$this->request
		);
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
		$should_increment = $ad_id && $is_ajax && $valid_hash;

		$this->request->expects($ad_id ? self::once() : self::never())
			->method('is_ajax')
			->willReturn($is_ajax);

		$this->request->expects(($ad_id && $is_ajax) ? self::once() : self::never())
			->method('variable')
			->with('hash', '')
			->willReturn($valid_hash ? generate_link_hash('phpbb_ads_click_' . $ad_id) : 'invalid');

		$this->manager->expects($should_increment ? self::once() : self::never())
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
	 * Hash generated for another ad cannot increment requested ad.
	 */
	public function test_click_hash_is_bound_to_ad_id()
	{
		$this->request->method('is_ajax')->willReturn(true);
		$this->request->method('variable')->with('hash', '')
			->willReturn(generate_link_hash('phpbb_ads_click_2'));
		$this->manager->expects(self::never())->method('increment_ad_clicks');

		$this->expectException('\phpbb\exception\http_exception');
		$this->get_controller()->handle(1, 'clicks');
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
		);
	}
	/**
	 * Test increment_views() method
	 *
	 * @dataProvider increment_views_data
	 */
	public function test_increment_views($ad_id, $is_ajax, $valid_hash)
	{
		$controller = $this->get_controller();
		$should_increment = (int) $ad_id > 0 && $is_ajax && $valid_hash;

		$this->request->expects(!empty($ad_id) ? self::once() : self::never())
			->method('is_ajax')
			->willReturn($is_ajax);

		$this->request->expects(($is_ajax && !empty($ad_id)) ? self::once() : self::never())
			->method('variable')
			->with('hash', '')
			->willReturn($valid_hash ? generate_link_hash('phpbb_ads_views_' . $ad_id) : 'invalid');

		$this->manager->expects($should_increment ? self::once() : self::never())
			->method('increment_ad_views')
			->with((int) $ad_id);

		try
		{
			$response = $controller->handle($ad_id, 'views');

			self::assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
		}
		catch (\phpbb\exception\http_exception $exception)
		{
			self::assertEquals(403, $exception->getStatusCode());
			self::assertEquals('NOT_AUTHORISED', $exception->getMessage());
		}
	}

	/**
	 * @dataProvider invalid_payload_data
	 */
	public function test_invalid_payload_is_rejected($data, $mode)
	{
		$this->request->expects(self::never())->method('is_ajax');
		$this->manager->expects(self::never())->method('increment_ad_views');
		$this->manager->expects(self::never())->method('increment_ad_clicks');

		$this->expectException('\phpbb\exception\http_exception');
		$this->get_controller()->handle($data, $mode);
	}

	public function invalid_payload_data()
	{
		return array(
			array('1-two', 'views'),
			array('1-2', 'views'),
			array('1', 'unknown'),
		);
	}
}
