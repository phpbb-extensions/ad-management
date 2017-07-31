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

require_once __DIR__ . '/../../../../../includes/functions_user.php';

class admin_input_test extends \phpbb_database_test_case
{
	/** @var bool A return value for check_form_key() */
	public static $valid_form = true;

	/** @var \phpbb\user */
	protected $user;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\language\language */
	protected $language;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\request\request */
	protected $request;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\ads\banner\banner */
	protected $banner;

	/**
	 * {@inheritDoc}
	 */
	static protected function setup_extensions()
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
	public function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;
		global $db;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);

		// Load/Mock classes required by the controller class
		$this->language = new \phpbb\language\language($lang_loader);
		$this->user = new \phpbb\user($this->language, '\phpbb\datetime');
		$this->user->timezone = new \DateTimeZone('UTC');
		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$this->banner = $this->getMockBuilder('\phpbb\ads\banner\banner')
			->disableOriginalConstructor()
			->getMock();

		// Global variables
		$db = $this->new_dbal();
	}

	/**
	 * Returns fresh new input controller.
	 *
	 * @return	\phpbb\ads\controller\admin_input	Admin input controller
	 */
	public function get_input_controller()
	{
		$input = new \phpbb\ads\controller\admin_input(
			$this->user,
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
			array(false, 'Ad Name #1', 'Ad Note #1', 'Ad Code #1', '', '', '', '5', '', '', '', 0, array('The submitted form was invalid. Try submitting again.')),
			array(true, '', 'Ad Note #1', 'Ad Code #1', '', '', '', '5', '', '', '', 0, array('AD_NAME_REQUIRED')),
			array(true, str_repeat('a', 256), 'Ad Note #1', 'Ad Code #1', '', '', '', '5', '', '', '', 0, array('AD_NAME_TOO_LONG')),
			array(true, 'Ad Name #1', 'Ad Note #1', 'Ad Code #1', '', '', 'blah', '5', '', '', '', 0, array('AD_END_DATE_INVALID')),
			array(true, 'Ad Name #1', 'Ad Note #1', 'Ad Code #1', '', '', '1970-01-01', '5', '', '', '', 0, array('AD_END_DATE_INVALID')),
			array(true, 'Ad Name #1', 'Ad Note #1', 'Ad Code #1', '', '', '', '0', '', '', '', 0, array('AD_PRIORITY_INVALID')),
			array(true, 'Ad Name #1', 'Ad Note #1', 'Ad Code #1', '', '', '', '11', '', '', '', 0, array('AD_PRIORITY_INVALID')),
			array(true, 'Ad Name #1', 'Ad Note #1', 'Ad Code #1', '', '', '', '5', '-1', '', '', 0, array('AD_VIEWS_LIMIT_INVALID')),
			array(true, 'Ad Name #1', 'Ad Note #1', 'Ad Code #1', '', '', '', '5', '', '-1', '', 0, array('AD_CLICKS_LIMIT_INVALID')),
			array(true, 'Ad Name #1', 'Ad Note #1', 'Ad Code #1', '', '', '', '5', '', '', 'adm', 0, array('AD_OWNER_INVALID')),
			array(false, '', 'Ad Note #1', 'Ad Code #1', '', '', 'blah', '0', '-1', '-1', 'adm', 0, array(
				'The submitted form was invalid. Try submitting again.',
				'AD_NAME_REQUIRED',
				'AD_END_DATE_INVALID',
				'AD_PRIORITY_INVALID',
				'AD_VIEWS_LIMIT_INVALID',
				'AD_CLICKS_LIMIT_INVALID',
				'AD_OWNER_INVALID',
			)),
			array(true, 'Ad Name #1', 'Ad Note #1', 'Ad Code #1', '1', array('above_header', 'above_footer'), '2033-01-01', '4', '50', '30', 'admin', '2', array()),
		);
	}

	/**
	 * Test get_form_data()
	 *
	 * @dataProvider get_form_data_data
	 */
	public function test_get_form_data($valid_form, $ad_name, $ad_note, $ad_code, $ad_enabled, $ad_locations, $ad_end_date, $ad_priority, $ad_views_limit, $ad_clicks_limit, $ad_owner, $ad_owner_expected, $errors)
	{
		self::$valid_form = $valid_form;
		$input_controller = $this->get_input_controller();

		$this->request->expects($this->exactly(10))
			->method('variable')
			->will($this->onConsecutiveCalls($ad_name, $ad_note, $ad_code, $ad_enabled, $ad_locations, $ad_end_date, $ad_priority, $ad_views_limit, $ad_clicks_limit, $ad_owner));

		$result = $input_controller->get_form_data('random string');

		if (!empty($errors))
		{
			$this->assertGreaterThan(0, $input_controller->has_errors());
			$this->assertEquals($errors, $input_controller->get_errors());
		}
		else
		{
			$this->assertEquals(array(
				'ad_name'         => $ad_name,
				'ad_note'         => $ad_note,
				'ad_code'         => $ad_code,
				'ad_enabled'      => $ad_enabled,
				'ad_locations'    => $ad_locations,
				'ad_end_date'     => $result['ad_end_date'], // Skipped, because it's different with every call
				'ad_priority'     => $ad_priority,
				'ad_views_limit'  => $ad_views_limit,
				'ad_clicks_limit' => $ad_clicks_limit,
				'ad_owner'        => $ad_owner_expected,
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
			array(false, false, false, array('CANNOT_CREATE_DIRECTORY'), '', ''),
			array(false, true, false, array('CANNOT_CREATE_DIRECTORY'), '', ''),
			array(false, true, true, array('CANNOT_CREATE_DIRECTORY'), '', ''),
			array(true, false, false, array('FILE_MOVE_UNSUCCESSFUL'), '', ''),
			array(true, true, false, array(), '', '<img src="http://images/phpbb_ads/abcdef.jpg" />'),
			array(true, true, false, array(), 'abc', "abc\n\n<img src=\"http://images/phpbb_ads/abcdef.jpg\" />"),
			array(true, true, true, array(), 'abc', "abc\n\n<img src=\"http://images/phpbb_ads/abcdef.jpg\" />"),
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

		$create_storage_dir = $this->banner->expects($this->once())
			->method('create_storage_dir');
		if (!$can_create_directory)
		{
			$create_storage_dir->willThrowException(new \phpbb\exception\runtime_exception('CANNOT_CREATE_DIRECTORY'));
		}
		else
		{
			$upload = $this->banner->expects($this->once())
				->method('upload');
			if (!$can_move_file)
			{
				$upload->willThrowException(new \phpbb\exception\runtime_exception('FILE_MOVE_UNSUCCESSFUL'));
			}
			else
			{
				$upload->willReturn('abcdef.jpg');
			}
		}

		if (!$can_create_directory || !$can_move_file)
		{
			$this->banner->expects($this->once())
				->method('remove');
		}

		$this->request->expects($this->once())
			->method('is_ajax')
			->willReturn($is_ajax);

		if ($is_ajax)
		{
			// Handle trigger_error() output called from json_response
			$this->setExpectedTriggerError(E_WARNING);
		}

		$result = $input_controller->banner_upload($ad_code);
		$this->assertEquals($ad_code_expected, $result);

		if (count($file_error))
		{
			$this->assertGreaterThan(0, $input_controller->has_errors());
			$this->assertEquals(array(implode('<br />', $file_error)), $input_controller->get_errors());
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
