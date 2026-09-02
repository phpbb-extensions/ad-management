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

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\user */
	protected $user;

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
		$user->ip = '192.0.2.1';
		$user->session_id = '';
		$this->user = $user;

		$this->manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$this->cache = $this->getMockBuilder('\phpbb\cache\driver\dummy')
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
			$this->request,
			$this->cache,
			$this->user
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
		$should_increment = $ad_id && $is_ajax && $valid_hash;

		$this->request->expects($ad_id ? self::once() : self::never())
			->method('is_ajax')
			->willReturn($is_ajax);

		$this->request->expects(($ad_id && $is_ajax) ? self::once() : self::never())
			->method('variable')
			->with('hash', '')
			->willReturn($valid_hash ? generate_link_hash('phpbb_ads_click_' . $ad_id) : 'invalid');

		$this->cache->expects($should_increment ? self::once() : self::never())
			->method('get')
			->willReturn(false);
		$this->cache->expects($should_increment ? self::once() : self::never())
			->method('put');

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
	 * Server cache suppresses rapid repeated clicks without database work.
	 */
	public function test_click_rate_limit()
	{
		$this->request->method('is_ajax')->willReturn(true);
		$this->request->method('variable')->with('hash', '')
			->willReturn(generate_link_hash('phpbb_ads_click_1'));
		$key = '_phpbb_ads_tracking_' . hash('sha256', 'clicks:192.0.2.1:1');
		$this->cache->expects(self::once())->method('get')->with($key)->willReturn(true);
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
		$this->request->method('is_ajax')->willReturn(true);
		$this->request->method('variable')->with('hash', '')
			->willReturn(generate_link_hash('phpbb_ads_click_1'));
		$key = '_phpbb_ads_tracking_' . hash('sha256', 'clicks:192.0.2.1:1');
		$this->cache->expects(self::once())->method('get')->with($key)->willReturn(false);
		$this->cache->expects(self::once())->method('put')
			->with($key, true, \phpbb\ads\controller\increment_controller::TRACKING_COOLDOWN);
		$this->manager->expects(self::once())->method('increment_ad_clicks')->with(1);

		$response = $this->get_controller()->handle(1, 'clicks');

		self::assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
	}

	/**
	 * New guest sessions from same IP remain inside same cooldown.
	 */
	public function test_click_rate_limit_does_not_use_session_id()
	{
		$this->user->session_id = 'new-session-id';
		$this->request->method('is_ajax')->willReturn(true);
		$this->request->method('variable')->with('hash', '')
			->willReturn(generate_link_hash('phpbb_ads_click_1'));
		$key = '_phpbb_ads_tracking_' . hash('sha256', 'clicks:192.0.2.1:1');
		$this->cache->expects(self::once())->method('get')->with($key)->willReturn(true);
		$this->cache->expects(self::never())->method('put');
		$this->manager->expects(self::never())->method('increment_ad_clicks');

		$response = $this->get_controller()->handle(1, 'clicks');

		self::assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
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
		$should_increment = (int) $ad_ids > 0 && $is_ajax && $valid_hash;
		$ad_count = $should_increment ? count(array_unique(explode('-', $ad_ids))) : 0;

		$this->request->expects(!empty($ad_ids) ? self::once() : self::never())
			->method('is_ajax')
			->willReturn($is_ajax);

		$this->request->expects(($is_ajax && !empty($ad_ids)) ? self::once() : self::never())
			->method('variable')
			->with('hash', '')
			->willReturn($valid_hash ? generate_link_hash('phpbb_ads_views_' . $ad_ids) : 'invalid');

		$this->cache->expects($ad_count ? self::exactly($ad_count) : self::never())
			->method('get')
			->willReturn(false);
		$this->cache->expects($ad_count ? self::exactly($ad_count) : self::never())
			->method('put');

		$this->manager->expects($should_increment ? self::once() : self::never())
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

	/**
	 * View cooldown is applied per advertisement inside a batch.
	 */
	public function test_view_rate_limit_filters_batch()
	{
		$this->request->method('is_ajax')->willReturn(true);
		$this->request->method('variable')->with('hash', '')
			->willReturn(generate_link_hash('phpbb_ads_views_1-2'));
		$key_one = '_phpbb_ads_tracking_' . hash('sha256', 'views:192.0.2.1:1');
		$key_two = '_phpbb_ads_tracking_' . hash('sha256', 'views:192.0.2.1:2');
		$this->cache->expects(self::exactly(2))->method('get')
			->withConsecutive(array($key_one), array($key_two))
			->willReturnOnConsecutiveCalls(true, false);
		$this->cache->expects(self::once())->method('put')
			->with($key_two, true, \phpbb\ads\controller\increment_controller::TRACKING_COOLDOWN);
		$this->manager->expects(self::once())->method('increment_ads_views')->with(array(2));

		$response = $this->get_controller()->handle('1-2', 'views');

		self::assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
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

	public function invalid_payload_data()
	{
		return array(
			array('1-two', 'views'),
			array(implode('-', array_fill(0, 51, '1')), 'views'),
			array('1', 'unknown'),
		);
	}
}
