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

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\request\request */
	protected $request;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\files\upload */
	protected $files_upload;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\filesystem\filesystem */
	protected $filesystem;

	/** @var string */
	protected $root_path;

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
		$lang = new \phpbb\language\language($lang_loader);

		// Load/Mock classes required by the controller class
		$this->user = new \phpbb\user($lang, '\phpbb\datetime');
		$this->user->timezone = new \DateTimeZone('UTC');
		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$this->files_upload = $this->getMockBuilder('\phpbb\files\upload')
			->disableOriginalConstructor()
			->getMock();
		$this->filesystem = $this->getMockBuilder('\phpbb\filesystem\filesystem')
			->disableOriginalConstructor()
			->getMock();
		$this->root_path = $phpbb_root_path;

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
			$this->request,
			$this->files_upload,
			$this->filesystem,
			$this->root_path
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
			array(false, false, false, array('CANNOT_CREATE_DIRECTORY', 'FILE_MOVE_UNSUCCESSFUL'), '', '<img src="http://images/phpbb_ads/" />'),
			array(false, false, true, array('CANNOT_CREATE_DIRECTORY'), '', '<img src="http://images/phpbb_ads/" />'),
			array(false, true, false, array('FILE_MOVE_UNSUCCESSFUL'), '', '<img src="http://images/phpbb_ads/" />'),
			array(false, true, true, array(), '', '<img src="http://images/phpbb_ads/abcdef.jpg" />'),
			array(true, false, false, array('FILE_MOVE_UNSUCCESSFUL'), '', '<img src="http://images/phpbb_ads/" />'),
			array(true, false, true, array(), '', '<img src="http://images/phpbb_ads/abcdef.jpg" />'),
			array(true, true, false, array('FILE_MOVE_UNSUCCESSFUL'), '', '<img src="http://images/phpbb_ads/" />'),
			array(true, true, true, array(), '', '<img src="http://images/phpbb_ads/abcdef.jpg" />'),
			array(true, true, true, array(), 'abc', "abc\n\n<img src=\"http://images/phpbb_ads/abcdef.jpg\" />"),
		);
	}

	/**
	 * Test banner_upload()
	 *
	 * @dataProvider banner_upload_data
	 */
	public function test_banner_upload($images_dir_exists, $can_create_directory, $can_move_file, $file_error, $ad_code, $ad_code_expected)
	{
		$input_controller = $this->get_input_controller();

		$this->files_upload->expects($this->once())
			->method('reset_vars');

		$this->files_upload->expects($this->once())
			->method('set_allowed_extensions')
			->with(array('gif', 'jpg', 'jpeg', 'png'));

		// Mock filespec
		$file = $this->getMockBuilder('\phpbb\files\filespec')
			->disableOriginalConstructor()
			->getMock();
		$file->error = $file_error;

		$this->files_upload->expects($this->once())
			->method('handle_upload')
			->with('files.types.form', 'banner')
			->willReturn($file);

		$file->expects($this->once())
			->method('clean_filename')
			->with('unique_ext');

		$this->filesystem->expects($this->once())
			->method('exists')
			->with($this->root_path . 'images/phpbb_ads')
			->willReturn($images_dir_exists);

		if (!$images_dir_exists)
		{
			$mkdir = $this->filesystem->expects($this->once())
				->method('mkdir')
				->with($this->root_path . 'images/phpbb_ads');

			if (!$can_create_directory)
			{
				$mkdir->willThrowException(new \phpbb\filesystem\exception\filesystem_exception('CANNOT_CREATE_DIRECTORY'));
			}
		}

		$file->expects($this->once())
			->method('move_file')
			->with('images/phpbb_ads')
			->willReturn($can_move_file);

		if (count($file_error))
		{
			$file->expects($this->once())
				->method('remove');
		}
		else
		{
			$file->expects($this->once())
				->method('get')
				->with('realname')
				->willReturn('abcdef.jpg');
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
