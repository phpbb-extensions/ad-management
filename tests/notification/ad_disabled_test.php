<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\notification;

class ad_disabled_test extends \phpbb_test_case
{
	/** @var \phpbb\ads\notification\type\ad_disabled */
	protected $notification;

	/** @var \phpbb\language\language|\PHPUnit\Framework\MockObject\MockObject */
	protected $language;

	/** @var \phpbb\notification\manager|\PHPUnit\Framework\MockObject\MockObject */
	protected $manager;

	/** @var \phpbb\db\driver\driver_interface|\PHPUnit\Framework\MockObject\MockObject */
	protected $db;

	protected function setUp(): void
	{
		parent::setUp();

		global $config, $phpbb_dispatcher, $phpbb_root_path, $phpEx, $user;

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
		$config = new \phpbb\config\config(array(
			'cookie_secure' => false,
			'force_server_vars' => true,
			'script_path' => '/phpbb',
			'server_name' => 'localhost',
			'server_port' => 80,
			'server_protocol' => 'http://',
		));

		$this->db = $this->getMockBuilder('\phpbb\db\driver\driver_interface')->getMock();
		$this->language = $this->getMockBuilder('\phpbb\language\language')->disableOriginalConstructor()->getMock();
		$user = $this->getMockBuilder('\phpbb\user')->disableOriginalConstructor()->getMock();
		$auth = $this->getMockBuilder('\phpbb\auth\auth')->getMock();
		$this->manager = $this->getMockBuilder('\phpbb\notification\manager')->disableOriginalConstructor()->getMock();
		$this->notification = new \phpbb\ads\notification\type\ad_disabled(
			$this->db,
			$this->language,
			$user,
			$auth,
			$phpbb_root_path,
			$phpEx,
			'phpbb_user_notifications'
		);

		$property = new \ReflectionProperty('\phpbb\notification\type\base', 'notification_manager');
		$property->setAccessible(true);
		$property->setValue($this->notification, $this->manager);
	}

	public function test_type_and_ids()
	{
		self::assertSame(\phpbb\ads\ext::NOTIFICATION_TYPE_DISABLED, $this->notification->get_type());
		self::assertSame(42, $this->notification::get_item_id(array('ad_id' => 42)));
		self::assertSame(0, $this->notification::get_item_parent_id(array('ad_id' => 42)));
		self::assertTrue($this->notification->is_available());
		self::assertSame('NOTIFICATION_TYPE_PHPBB_ADS_AD_DISABLED', $this->notification::$notification_option['lang']);
	}

	public function test_owner_receives_default_methods()
	{
		$this->db->method('sql_in_set')->willReturn('user_id = 2');
		$this->db->method('sql_query')->willReturn('result');
		$this->db->method('sql_fetchrow')->willReturn(false);
		$this->manager->expects(self::once())
			->method('get_default_methods')
			->willReturn(array('notification.method.board'));

		self::assertSame(array(
			2 => array('notification.method.board'),
		), $this->notification->find_users_for_notification(array('ad_owner' => 2)));
		self::assertSame(array(), $this->notification->find_users_for_notification(array('ad_owner' => ANONYMOUS)));
	}

	public function test_owner_notification_preferences_are_honoured()
	{
		$this->db->expects(self::once())
			->method('sql_in_set')
			->with('user_id', array(2))
			->willReturn('user_id = 2');
		$this->db->method('sql_query')->willReturn('result');
		$this->db->expects(self::exactly(4))
			->method('sql_fetchrow')
			->with('result')
			->willReturnOnConsecutiveCalls(
				array('user_id' => 2, 'method' => 'notification.method.board', 'notify' => 0),
				array('user_id' => 2, 'method' => 'notification.method.email', 'notify' => 1),
				array('user_id' => 2, 'method' => 'notification.method.phpbb.wpn.webpush', 'notify' => 1),
				false
			);
		$this->manager->method('get_default_methods')->willReturn(array('notification.method.board'));

		self::assertSame(array(
			2 => array('notification.method.email', 'notification.method.phpbb.wpn.webpush'),
		), $this->notification->find_users_for_notification(array('ad_owner' => 2)));
	}

	public function test_title()
	{
		$this->set_data('ad_name', 'Example');
		$this->language->expects(self::once())->method('add_lang')->with('common', 'phpbb/ads');
		$this->language->expects(self::once())
			->method('lang')
			->with('PHPBB_ADS_NOTIFICATION_DISABLED', 'Example')
			->willReturn('Advertisement disabled');

		self::assertSame('Advertisement disabled', $this->notification->get_title());
	}

	public function test_reason()
	{
		$this->set_data('reason', \phpbb\ads\ad\manager::DISABLED_END_DATE);
		$this->language->expects(self::once())->method('add_lang')->with('common', 'phpbb/ads');
		$this->language->expects(self::once())
			->method('lang')
			->with('PHPBB_ADS_NOTIFICATION_REASON_END_DATE')
			->willReturn('Expiration date reached');

		self::assertSame('Expiration date reached', $this->notification->get_reason());
	}

	public function test_reference_and_email_template()
	{
		self::assertSame('', $this->notification->get_reference());
		self::assertSame('@phpbb_ads/ad_disabled', $this->notification->get_email_template());
		self::assertSame(array(), $this->notification->users_to_query());
	}

	public function url_data()
	{
		return array(
			'relative URL' => array(\Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_PATH, false),
			'absolute URL' => array(\Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL, true),
		);
	}

	/**
	 * @dataProvider url_data
	 */
	public function test_url($reference_type, $absolute)
	{
		global $phpbb_root_path;

		$root = $absolute ? generate_board_url() . '/' : $phpbb_root_path;
		self::assertSame(
			$root . 'ucp.php?i=-phpbb-ads-ucp-main_module&amp;mode=stats',
			$this->notification->get_url($reference_type)
		);
	}

	public function test_email_template_variables()
	{
		$this->set_data('ad_name', 'Example &amp; Ad');
		$this->set_data('reason', \phpbb\ads\ad\manager::DISABLED_END_DATE);
		$this->language->expects(self::once())->method('add_lang')->with('common', 'phpbb/ads');
		$this->language->expects(self::once())
			->method('lang')
			->with('PHPBB_ADS_NOTIFICATION_REASON_END_DATE')
			->willReturn('<em>Expiration date reached</em>');

		self::assertSame(array(
			'AD_NAME' => 'Example & Ad',
			'REASON' => 'Expiration date reached',
			'U_VIEW_ADS' => 'http://localhost/phpbb/ucp.php?i=-phpbb-ads-ucp-main_module&mode=stats',
		), $this->notification->get_email_template_variables());
	}

	public function test_create_insert_array_preserves_ad_data()
	{
		$type_data = array(
			'ad_id' => 42,
			'ad_name' => 'Example ad',
			'reason' => \phpbb\ads\ad\manager::DISABLED_END_DATE,
		);

		$this->notification->create_insert_array($type_data);
		$insert = $this->notification->get_insert_array();

		self::assertSame(42, $insert['item_id']);
		self::assertSame(0, $insert['item_parent_id']);
		self::assertSame(array(
			'ad_name' => 'Example ad',
			'reason' => \phpbb\ads\ad\manager::DISABLED_END_DATE,
		), unserialize($insert['notification_data']));
	}

	protected function set_data($key, $value, $notification = null)
	{
		$method = new \ReflectionMethod('\phpbb\notification\type\base', 'set_data');
		$method->setAccessible(true);
		$method->invoke($notification ?: $this->notification, $key, $value);
	}
}
