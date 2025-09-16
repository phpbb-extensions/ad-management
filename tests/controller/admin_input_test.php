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

use DateTimeZone;
use phpbb\ads\banner\banner;
use phpbb\avatar\helper as avatar_helper;
use phpbb\config\config;
use phpbb\datetime;
use phpbb\exception\runtime_exception;
use phpbb\language\language;
use phpbb\language\language_file_loader;
use phpbb\request\request;
use phpbb\symfony_request;
use phpbb\user;
use phpbb\user_loader;
use phpbb_database_test_case;
use phpbb_mock_request;
use PHPUnit\Framework\MockObject\MockObject;

class admin_input_test extends phpbb_database_test_case
{
	public static bool $valid_form = true;
	protected user $user;
	protected user_loader $user_loader;
	protected language $language;
	protected MockObject|request $request;
	protected banner|MockObject $banner;

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
		return $this->createXMLDataSet(__DIR__ . '/../fixtures/ad.xml');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		global $config, $db, $request, $symfony_request, $user, $phpbb_root_path, $phpEx, $phpbb_dispatcher;

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		// Global variables
		$db = $this->new_dbal();

		// Load/Mock classes required by the controller class
		$this->language = new language(new language_file_loader($phpbb_root_path, $phpEx));
		$this->user = $user = new user($this->language, datetime::class);
		$this->user->timezone = new DateTimeZone('UTC');
		$avatar_helper = $this->getMockBuilder(avatar_helper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->user_loader = new user_loader($avatar_helper, $db, $phpbb_root_path, $phpEx, 'phpbb_users');
		$this->request = $this->getMockBuilder(request::class)
			->disableOriginalConstructor()
			->getMock();
		$this->banner = $this->getMockBuilder(banner::class)
			->disableOriginalConstructor()
			->getMock();

		// Global objects required by generate_board_url()
		$config = new config(array(
			'script_path'           => '/phpbb',
			'server_name'           => 'localhost',
			'server_port'           => 80,
			'server_protocol'       => 'http://',
		));
		$request = new phpbb_mock_request;
		$symfony_request = new symfony_request($request);
	}

	/**
	 * Returns fresh new input controller.
	 *
	 * @return	\phpbb\ads\controller\admin_input	Admin input controller
	 */
	public function get_input_controller(): admin_input
	{
		return new class($this->user, $this->user_loader, $this->language, $this->request, $this->banner) extends \phpbb\ads\controller\admin_input {
			protected function send_ajax_response($success, $text): void
			{
				if ($this->request->is_ajax())
				{
					echo json_encode([
						'success' => $success
					], JSON_THROW_ON_ERROR);
				}
			}
		};
	}

	/**
	 * Data for test_get_form_data
	 *
	 * @return array Array of test data
	 */
	public static function get_form_data_data(): array
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
	public static function banner_upload_data(): array
	{
		return array(
			array(false, false, false, array('CANNOT_CREATE_DIRECTORY'), '', ''),
			array(false, true, false, array('CANNOT_CREATE_DIRECTORY'), '', ''),
			array(false, true, true, array('CANNOT_CREATE_DIRECTORY'), '', ''),
			array(true, false, false, array('FILE_MOVE_UNSUCCESSFUL'), '', ''),
			array(true, true, false, array(), '', '<img src="http://localhost/phpbb/images/phpbb_ads/abcdef.jpg">'),
			array(true, true, false, array(), 'abc', "abc\n\n<img src=\"http://localhost/phpbb/images/phpbb_ads/abcdef.jpg\">"),
			array(true, true, true, array(), 'abc', "abc\n\n<img src=\"http://localhost/phpbb/images/phpbb_ads/abcdef.jpg\">"),
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

		$create_storage_dir = $this->banner->expects(self::once())
			->method('create_storage_dir');
		if (!$can_create_directory)
		{
			$create_storage_dir->willThrowException(new runtime_exception('CANNOT_CREATE_DIRECTORY'));
		}
		else
		{
			$upload = $this->banner->expects(self::once())
				->method('upload');
			if (!$can_move_file)
			{
				$upload->willThrowException(new runtime_exception('FILE_MOVE_UNSUCCESSFUL'));
			}
			else
			{
				$upload->willReturn('abcdef.jpg');
			}
		}

		if (!$can_create_directory || !$can_move_file)
		{
			$this->banner->expects(self::once())
				->method('remove');
		}

		$this->request->expects(self::atLeast(1))
			->method('is_ajax')
			->willReturn($is_ajax);

		if ($is_ajax)
		{
			// Handle trigger_error() output called from json_response
			$this->expectOutputString('{"success":' . (count($file_error) ? 'false' : 'true') . '}');
		}

		$result = $input_controller->banner_upload($ad_code);
		self::assertEquals($ad_code_expected, $result);

		if (count($file_error))
		{
			self::assertGreaterThan(0, $input_controller->has_errors());
			self::assertEquals(array(implode('<br>', $file_error)), $input_controller->get_errors());
		}
	}
}

/**
 * Mock check_form_key()
 * Note: use the same namespace as the admin_input
 *
 * @return bool
 */
function check_form_key(): bool
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
