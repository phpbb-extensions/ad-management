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

class admin_input_test extends \phpbb_database_test_case
{
	/** @var bool A return value for check_form_key() */
	public static $valid_form = true;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\request\request */
	protected $request;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\ads\banner\banner */
	protected $banner;

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
		return $this->createXMLDataSet(__DIR__ . '/../fixtures/ad.xml');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		global $config, $db, $request, $symfony_request, $user, $phpbb_root_path, $phpEx;

		// Global variables
		$db = $this->new_dbal();

		// Load/Mock classes required by the controller class
		$this->language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$this->controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$this->user = $user = new \phpbb\user($this->language, '\phpbb\datetime');
		$this->user->timezone = new \DateTimeZone('UTC');
		$avatar_helper = $this->getMockBuilder('\phpbb\avatar\helper')
			->disableOriginalConstructor()
			->getMock();
		$this->user_loader = new \phpbb\user_loader($avatar_helper, $db, $phpbb_root_path, $phpEx, 'phpbb_users');
		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$this->banner = $this->getMockBuilder('\phpbb\ads\banner\banner')
			->disableOriginalConstructor()
			->getMock();

		// Global objects required by generate_board_url()
		$config = new \phpbb\config\config(array(
			'script_path'           => '/phpbb',
			'server_name'           => 'localhost',
			'server_port'           => 80,
			'server_protocol'       => 'http://',
		));
		$request = new \phpbb_mock_request;
		$symfony_request = new \phpbb\symfony_request($request);
	}

	/**
	 * Returns fresh new input controller.
	 *
	 * @return	\phpbb\ads\controller\admin_input	Admin input controller
	 */
	public function get_input_controller()
	{
		$input = new \phpbb\ads\controller\admin_input(
			$this->controller_helper,
			$this->user,
			$this->user_loader,
			$this->language,
			$this->request,
			$this->banner
		);

		return $input;
	}

	/**
	 * Data for test_get_form_data
	 *
	 * @return array Array of test data
	 */
	public function get_form_data_data()
	{
		return array(
			array(false, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 5, 0, 0, 0, '', [], false], 0, ['FORM_INVALID']),
			array(true, ['', 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 5, 0, 0, 0, '', [], false], 0, ['AD_NAME_REQUIRED']),
			array(true, [str_repeat('a', 256), 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 5, 0, 0, 0, '', [], false], 0, ['AD_NAME_TOO_LONG']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code with emoji 😀', 0, '', '', '', 5, 0, 0, 0, '', [], false], 0, ['AD_CODE_ILLEGAL_CHARS']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', 'blah', '', 5, 0, 0, 0, '', [], false], 0, ['AD_START_DATE_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', 'blah', 5, 0, 0, 0, '', [], false], 0, ['AD_END_DATE_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '1970-01-01', '', 5, 0, 0, 0, '', [], false], 0, ['AD_START_DATE_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', '1970-01-01', 5, 0, 0, 0, '', [], false], 0, ['AD_END_DATE_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '2060-01-01', '2050-01-01', 5, 0, 0, 0, '', [], false], 0, ['END_DATE_TOO_SOON']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 0, 0, 0, 0, '', [], false], 0, ['AD_PRIORITY_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 11, 0, 0, 0, '', [], false], 0, ['AD_PRIORITY_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 5, 0, -1, 0, '', [], false], 0, ['AD_VIEWS_LIMIT_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 5, 0, 0, -1, '', [], false], 0, ['AD_CLICKS_LIMIT_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 5, 0, 0, 0, 'adm', [], false], 0, ['AD_OWNER_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 5, 0, 0, 0, 'adm', [], false], 0, ['AD_OWNER_INVALID']),
			array(false, ['', 'Ad Note #1', 'Ad Code #1', 0, '', 'blah', 'blah', 0, 0, -1, -1, 'adm', [], false], 0, [
				'FORM_INVALID',
				'AD_NAME_REQUIRED',
				'AD_START_DATE_INVALID',
				'AD_END_DATE_INVALID',
				'AD_PRIORITY_INVALID',
				'AD_VIEWS_LIMIT_INVALID',
				'AD_CLICKS_LIMIT_INVALID',
				'AD_OWNER_INVALID',
			]),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', '1', array('above_header', 'above_footer'), '2018-01-01', '2033-01-01', '4', '1', '50', '30', 'admin', ['5'], 0], 2, []),
		);
	}

	/**
	 * Test get_form_data()
	 *
	 * @dataProvider get_form_data_data
	 */
	public function test_get_form_data($valid_form, $data, $ad_owner_expected, $errors)
	{
		[$ad_name, $ad_note, $ad_code, $ad_enabled, $ad_locations, $ad_start_date, $ad_end_date, $ad_priority, $ad_content_only, $ad_views_limit, $ad_clicks_limit, $ad_owner, $ad_groups, $ad_centering] = $data;

		self::$valid_form = $valid_form;
		$input_controller = $this->get_input_controller();

		$this->request->expects(self::exactly(14))
			->method('variable')
			->will(self::onConsecutiveCalls($ad_name, $ad_note, $ad_code, $ad_enabled, $ad_locations, $ad_start_date, $ad_end_date, $ad_priority, $ad_content_only, $ad_views_limit, $ad_clicks_limit, $ad_owner, $ad_groups, $ad_centering));

		$result = $input_controller->get_form_data();

		if (!empty($errors))
		{
			self::assertGreaterThan(0, $input_controller->has_errors());
			self::assertEquals($errors, $input_controller->get_errors());
		}
		else
		{
			self::assertEquals(array(
				'ad_name'         => $ad_name,
				'ad_note'         => $ad_note,
				'ad_code'         => $ad_code,
				'ad_enabled'      => $ad_enabled,
				'ad_locations'    => $ad_locations,
				'ad_start_date'   => $result['ad_start_date'], // Skipped, because it's different with every call
				'ad_end_date'     => $result['ad_end_date'], // Skipped, because it's different with every call
				'ad_priority'     => $ad_priority,
				'ad_content_only' => $ad_content_only,
				'ad_views_limit'  => $ad_views_limit,
				'ad_clicks_limit' => $ad_clicks_limit,
				'ad_owner'        => $ad_owner_expected,
				'ad_groups'		  => $ad_groups,
				'ad_centering'	  => $ad_centering,
			), $result);
		}
	}

	/**
	 * Data for test_banner_upload
	 *
	 * @return array Array of test data
	 */
	public function banner_upload_data()
	{
		return array(
			array(true, false, false, array('FILE_MOVE_UNSUCCESSFUL'), '', ''),
			array(true, true, false, array(), '', '<img src="http://localhost/phpbb/images/phpbb_ads/abcdef.jpg" />'),
			array(true, true, false, array(), 'abc', "abc\n\n<img src=\"http://localhost/phpbb/images/phpbb_ads/abcdef.jpg\" />"),
			array(true, true, true, array(), 'abc', "abc\n\n<img src=\"http://loscalhost/phpbb/images/phpbb_ads/abcdef.jpg\" />"),
		);
	}

	/**
	 * Test banner_upload()
	 *
	 * @dataProvider banner_upload_data
	 */
	public function test_banner_upload($can_create_directory, $can_move_file, $is_ajax, $file_error, $ad_code, $ad_code_expected)
	{
		$input_controller = $this->get_input_controller();

		$upload = $this->banner->expects(self::once())
			->method('upload');
		if (!$can_move_file)
		{
			$upload->willThrowException(new \phpbb\exception\runtime_exception('FILE_MOVE_UNSUCCESSFUL'));
		}
		else
		{
			$upload->willReturn('abcdef.jpg');
		}

		if (!$can_create_directory || !$can_move_file)
		{
			$this->banner->expects(self::once())
				->method('remove');
		}

		$this->controller_helper->method('route')
			->willReturn('/images/phpbb_ads/abcdef.jpg');

		$this->request->expects(self::once())
			->method('is_ajax')
			->willReturn($is_ajax);

		if ($is_ajax)
		{
			// Handle trigger_error() output called from json_response
			$this->setExpectedTriggerError(E_WARNING);
		}

		$result = $input_controller->banner_upload($ad_code);
		self::assertEquals($ad_code_expected, $result);

		if (count($file_error))
		{
			self::assertGreaterThan(0, $input_controller->has_errors());
			self::assertEquals(array(implode('<br />', $file_error)), $input_controller->get_errors());
		}
	}
}

/**
 * Mock check_form_key()
 * Note: use the same namespace as the admin_input
 *
 * @return bool
 */
function check_form_key()
{
	return \phpbb\ads\controller\admin_input_test::$valid_form;
}

/**
 * Mock add_form_key()
 * Note: use the same namespace as the admin_input
 */
function add_form_key()
{
}
