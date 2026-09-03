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

require_once __DIR__ . '/admin_test_helpers.php';

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
	protected user $user;
	protected user_loader $user_loader;
	protected language $language;
	protected MockObject|request $request;
	protected banner|MockObject $banner;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\ads\ad\manager */
	protected $manager;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\ads\location\manager */
	protected $location_manager;

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
		$this->manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->manager->method('load_groups')->willReturn(array(
			array('group_id' => '1'),
			array('group_id' => '2'),
		));
		$this->location_manager = $this->getMockBuilder('\phpbb\ads\location\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->location_manager->method('get_all_locations')->with(false)->willReturn(array(
			'above_header' => array(),
			'above_footer' => array(),
		));

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
		return new \phpbb\ads\controller\admin_input(
			$this->user,
			$this->user_loader,
			$this->language,
			$this->request,
			$this->banner,
			$this->manager,
			$this->location_manager
		);
	}

	/**
	 * Data for test_get_form_data
	 *
	 * @return array Array of test data
	 */
	public static function get_form_data_data(): array
	{
		return array(
			array(false, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 5, 0, '', [], false, 1], 0, ['FORM_INVALID']),
			array(true, ['Ad Name 😀', 'Ad Note 📝', 'Ad Code #1', 0, '', '', '', 5, 0, '', [], false, 1], 0, []),
			array(true, ['Ad Name 日本語 Ελληνικά', 'Ad Note Кириллица 中文', 'Ad Code #1', 0, '', '', '', 5, 0, '', [], false, 1], 0, []),
			array(true, [str_repeat('😀', 28), 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 5, 0, '', [], false, 1], 0, []),
			array(true, ['', 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 5, 0, '', [], false, 1], 0, ['AD_NAME_REQUIRED']),
			array(true, [str_repeat('a', 256), 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 5, 0, '', [], false, 1], 0, ['AD_NAME_TOO_LONG']),
			array(true, [str_repeat('😀', 29), 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 5, 0, '', [], false, 1], 0, ['AD_NAME_TOO_LONG']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code with emoji 😀', 0, '', '', '', 5, 0, '', [], false, 1], 0, ['AD_CODE_ILLEGAL_CHARS']),
			// Invalid and duplicate location/group IDs are removed.
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, ['above_header', 'invalid', 'above_header'], '', '', 5, 0, '', [2, 999, 2], false, 1], 0, []),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', 'blah', '', 5, 0, '', [], false, 1], 0, ['AD_START_DATE_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', 'blah', 5, 0, '', [], false, 1], 0, ['AD_END_DATE_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '2060-02-30', '', 5, 0, '', [], false, 1], 0, ['AD_START_DATE_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', '2060-02-30', 5, 0, '', [], false, 1], 0, ['AD_END_DATE_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '1970-01-01', '', 5, 0, '', [], false, 1], 0, ['AD_START_DATE_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', '1970-01-01', 5, 0, '', [], false, 1], 0, ['AD_END_DATE_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '2060-01-01', '2050-01-01', 5, 0, '', [], false, 1], 0, ['END_DATE_TOO_SOON']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 0, 0, '', [], false, 1], 0, ['AD_PRIORITY_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 11, 0, '', [], false, 1], 0, ['AD_PRIORITY_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 5, 0, 'adm', [], false, 1], 0, ['AD_OWNER_INVALID']),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', 0, '', '', '', 5, 0, 'adm', [], false, 1], 0, ['AD_OWNER_INVALID']),
			array(false, ['', 'Ad Note #1', 'Ad Code #1', 0, '', 'blah', 'blah', 0, 0, 'adm', [], false, 1], 0, [
				'FORM_INVALID',
				'AD_NAME_REQUIRED',
				'AD_START_DATE_INVALID',
				'AD_END_DATE_INVALID',
				'AD_PRIORITY_INVALID',
				'AD_OWNER_INVALID',
			]),
			array(true, ['Ad Name #1', 'Ad Note #1', 'Ad Code #1', '1', array('above_header', 'above_footer'), '2018-01-01', '2019-01-01', '4', '1', 'admin', ['2'], 0, 0, 1, 1], 2, [], strtotime('2018-01-01 12:34:56 UTC'), strtotime('2019-01-01 12:34:56 UTC')),
		);
	}

	/**
	 * Test get_form_data()
	 *
	 * @dataProvider get_form_data_data
	 */
	public function test_get_form_data($valid_form, $data, $ad_owner_expected, $errors, $existing_start_date = 0, $existing_end_date = 0)
	{
		[$ad_name, $ad_note, $ad_code, $ad_enabled, $ad_locations, $ad_start_date, $ad_end_date, $ad_priority, $ad_content_only, $ad_owner, $ad_groups, $ad_centering, $ad_consent] = $data;
		$ad_views_enabled = isset($data[13]) ? $data[13] : 0;
		$ad_clicks_enabled = isset($data[14]) ? $data[14] : 0;
		$uploaded_banners = isset($data[15]) ? $data[15] : array();

		admin_test_state::$valid_form = $valid_form;
		$input_controller = $this->get_input_controller();

		$this->request->expects(self::exactly(16))
			->method('variable')
			->will(self::onConsecutiveCalls($ad_name, $ad_note, $ad_code, $ad_enabled, $ad_locations, $ad_start_date, $ad_end_date, $ad_priority, $ad_content_only, $ad_owner, $ad_groups, $ad_centering, $ad_consent, $ad_views_enabled, $ad_clicks_enabled, $uploaded_banners));

		$result = $input_controller->get_form_data($existing_start_date, $existing_end_date);

		if (!empty($errors))
		{
			self::assertGreaterThan(0, $input_controller->has_errors());
			self::assertEquals($errors, $input_controller->get_errors());
		}
		else
		{
			$expected_locations = array_values(array_unique(array_intersect((array) $ad_locations, array('above_header', 'above_footer'))));
			$expected_groups = array_values(array_unique(array_intersect((array) $ad_groups, array(1, 2))));

			self::assertEquals(array(
				'ad_name'         => utf8_encode_ncr($ad_name),
				'ad_note'         => utf8_encode_ncr($ad_note),
				'ad_code'         => $ad_code,
				'ad_enabled'      => $ad_enabled,
				'ad_locations'    => $expected_locations,
				'ad_start_date'   => $result['ad_start_date'], // Skipped, because it's different with every call
				'ad_end_date'     => $result['ad_end_date'], // Skipped, because it's different with every call
				'ad_priority'     => $ad_priority,
				'ad_content_only' => $ad_content_only,
				'ad_owner'        => $ad_owner_expected,
				'ad_groups'		  => $expected_groups,
				'ad_centering'	  => $ad_centering,
				'ad_consent'	  => $ad_consent,
				'ad_views_enabled' => $ad_views_enabled,
				'ad_clicks_enabled' => $ad_clicks_enabled,
				'uploaded_banners' => $uploaded_banners,
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
			array(false, false, false, array('CANNOT_INITIALIZE_STORAGE'), '', ''),
			array(false, true, false, array('CANNOT_INITIALIZE_STORAGE'), '', ''),
			array(false, true, true, array('CANNOT_INITIALIZE_STORAGE'), '', ''),
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
			$create_storage_dir->willThrowException(new \phpbb\filesystem\exception\filesystem_exception('FILESYSTEM_CANNOT_CREATE_DIRECTORY'));
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

		$this->request->expects(self::once())
			->method('is_ajax')
			->willReturn($is_ajax);

		if ($is_ajax)
		{
			$text = !empty($file_error) ? '"' . $file_error[0] . '"' : '"' . addcslashes(trim(substr($ad_code_expected, strpos($ad_code_expected, '<img'))), "/\"") . '"';
			$filename = empty($file_error) ? '"filename":"abcdef.jpg",' : '';
			$this->expectOutputString('{' . $filename . '"success":' . (count($file_error) ? 'false' : 'true') . ',"title":"Information","text":' . $text . '}');
			$this->expectException(\RuntimeException::class);
		}

		$uploaded_banners = array();
		$result = $input_controller->banner_upload($ad_code, $uploaded_banners);
		self::assertEquals($ad_code_expected, $result);
		self::assertSame($can_create_directory && $can_move_file ? array('abcdef.jpg') : array(), $uploaded_banners);

		if (count($file_error))
		{
			self::assertGreaterThan(0, $input_controller->has_errors());
			self::assertEquals(array(implode('<br>', $file_error)), $input_controller->get_errors());
		}
	}
}
