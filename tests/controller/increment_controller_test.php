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

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\user */
	protected $user;

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

		global $user;
		$user = $this->getMockBuilder('\phpbb\user')->disableOriginalConstructor()->getMock();
		$user->data = array('user_form_salt' => 'test-form-salt');
		$user->session_id = '';
		$this->user = $user;

		$this->manager = $this->getMockBuilder(manager::class)
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMockBuilder(request::class)
			->disableOriginalConstructor()
			->getMock();
		$this->cache = $this->getMockBuilder('\phpbb\cache\driver\dummy')
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
			$this->request,
			$this->cache,
			$this->user
		);
	}

	/**
	 * Test data for the test_increment_clicks() function
	 *
	 * @return array Array of test data
	 */
	public static function increment_clicks_data(): array
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
			->willReturn($valid_hash ? generate_link_hash('phpbb_ads_click_' . $ad_id) : 'invalid');

		$this->manager->expects(($ad_id && $is_ajax && $valid_hash) ? self::once() : self::never())
			->method('increment_ad_clicks')
			->with($ad_id);

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
	 * Server cache suppresses rapid repeated clicks without database work.
	 */
	public function test_click_rate_limit()
	{
		$this->user->session_id = 'session-id';
		$this->request->method('is_ajax')->willReturn(true);
		$this->request->method('variable')->with('hash', '')
			->willReturn(generate_link_hash('phpbb_ads_click_1'));
		$this->cache->expects(self::once())->method('get')->willReturn(true);
		$this->cache->expects(self::never())->method('put');
		$this->manager->expects(self::never())->method('increment_ad_clicks');

		$response = $this->get_controller()->handle(1, 'clicks');

		self::assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
	}

	/**
	 * First click stores cooldown and increments counter.
	 */
	public function test_click_rate_limit_starts_cooldown()
	{
		$this->user->session_id = 'session-id';
		$this->request->method('is_ajax')->willReturn(true);
		$this->request->method('variable')->with('hash', '')
			->willReturn(generate_link_hash('phpbb_ads_click_1'));
		$key = '_phpbb_ads_click_' . hash('sha256', 'session-id:1');
		$this->cache->expects(self::once())->method('get')->with($key)->willReturn(false);
		$this->cache->expects(self::once())->method('put')
			->with($key, true, \phpbb\ads\controller\increment_controller::CLICK_COOLDOWN);
		$this->manager->expects(self::once())->method('increment_ad_clicks')->with(1);

		$response = $this->get_controller()->handle(1, 'clicks');

		self::assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
	}


	/**
	 * Test data for the test_increment_clicks() function
	 *
	 * @return array Array of test data
	 */
	public static function increment_views_data(): array
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

			self::assertInstanceOf(JsonResponse::class, $response);
		}
		catch (http_exception $exception)
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
		$this->manager->expects(self::never())->method('increment_ads_views');
		$this->manager->expects(self::never())->method('increment_ad_clicks');

		$this->expectException('\phpbb\exception\http_exception');
		$this->get_controller()->handle($data, $mode);
	}

	public static function invalid_payload_data()
	{
		return array(
			array('1-two', 'views'),
			array(implode('-', array_fill(0, 51, '1')), 'views'),
			array('1', 'unknown'),
		);
	}
}
