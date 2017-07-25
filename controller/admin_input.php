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

use \phpbb\ads\controller\admin_controller as controller;

/**
 * Admin input
 */
class admin_input
{
	const MAX_NAME_LENGTH = 255;
	const DEFAULT_PRIORITY = 5;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\files\upload */
	protected $files_upload;

	/** @var \phpbb\filesystem\filesystem_interface */
	protected $filesystem;

	/** @var string */
	protected $root_path;

	/** @var array Form validation errors */
	protected $errors = array();

	/**
	 * Constructor
	 *
	 * @param \phpbb\user								$user			User object
	 * @param \phpbb\request\request					$request		Request object
	 * @param \phpbb\files\upload						$files_upload	Files upload object
	 * @param \phpbb\filesystem\filesystem_interface	$filesystem		Filesystem object
	 * @param string									$root_path		Root path
	 */
	public function __construct(\phpbb\user $user, \phpbb\request\request $request, \phpbb\files\upload $files_upload, \phpbb\filesystem\filesystem_interface $filesystem, $root_path)
	{
		$this->user = $user;
		$this->request = $request;
		$this->files_upload = $files_upload;
		$this->filesystem = $filesystem;
		$this->root_path = $root_path;
	}

	public function get_errors()
	{
		return $this->errors;
	}

	public function has_errors()
	{
		return count($this->errors);
	}

	/**
	 * Get admin form data.
	 *
	 * @param	string	$form_name	The form name.
	 * @return	array	Form data
	 */
	public function get_form_data($form_name)
	{
		$data = array(
			'ad_name'         => $this->request->variable('ad_name', '', true),
			'ad_note'         => $this->request->variable('ad_note', '', true),
			'ad_code'         => $this->request->variable('ad_code', '', true),
			'ad_enabled'      => $this->request->variable('ad_enabled', 0),
			'ad_locations'    => $this->request->variable('ad_locations', array('')),
			'ad_end_date'     => $this->request->variable('ad_end_date', ''),
			'ad_priority'     => $this->request->variable('ad_priority', self::DEFAULT_PRIORITY),
			'ad_views_limit'  => $this->request->variable('ad_views_limit', 0),
			'ad_clicks_limit' => $this->request->variable('ad_clicks_limit', 0),
			'ad_owner'        => $this->request->variable('ad_owner', '', true),
		);

		// Validate form key
		if (!check_form_key($form_name))
		{
			$this->errors[] = $this->user->lang('FORM_INVALID');
		}

		// Validate each property. Every method adds errors directly to $this->errors.
		foreach ($data as $prop_name => $prop_val)
		{
			if (method_exists($this, 'validate_' . $prop_name))
			{
				$this->{'validate_' . $prop_name}($prop_val);
			}
		}

		// Replace end date and owner with IDs that will be stored in the DB
		$data['ad_end_date'] = $this->end_date_to_timestamp($data['ad_end_date']);
		$data['ad_owner'] = $this->owner_to_id($data['ad_owner']);

		return $data;
	}

	/**
	 * Upload image and return updated ad code or <img> of new banner when using ajax.
	 *
	 * @param	 string	 $ad_code	 Current ad code
	 * @return	 mixed	 \phpbb\json_response when request is ajax or updated ad code otherwise.
	 */
	public function banner_upload($ad_code)
	{
		// Set file restrictions
		$this->files_upload->reset_vars();
		$this->files_upload->set_allowed_extensions(array('gif', 'jpg', 'jpeg', 'png'));

		// Upload file
		$file = $this->files_upload->handle_upload('files.types.form', 'banner');
		$file->clean_filename('unique_ext');

		// First lets create phpbb_ads directory if needed
		if (!$this->filesystem->exists($this->root_path . 'images/phpbb_ads'))
		{
			try
			{
				$this->filesystem->mkdir($this->root_path . 'images/phpbb_ads');
			}
			catch (\phpbb\filesystem\exception\filesystem_exception $e)
			{
				$file->set_error($this->user->lang($e->getMessage()));
			}
		}

		// Move file to proper location
		if (!$file->move_file('images/phpbb_ads'))
		{
			$file->set_error($this->user->lang('FILE_MOVE_UNSUCCESSFUL'));
		}

		// Problem with uploading
		if (count($file->error))
		{
			$file->remove();
			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send(array(
					'success'	=> false,
					'title'		=> $this->user->lang('INFORMATION'),
					'text'		=> implode('<br />', $file->error),
				));
			}
			else
			{
				$this->errors[] = implode('<br />', $file->error);
			}
		}
		else
		{
			$banner_html = '<img src="' . generate_board_url() . '/images/phpbb_ads/' . $file->get('realname') . '" />';

			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send(array(
					'success'	=> true,
					'text'		=> $banner_html,
				));
			}

			return ($ad_code ? $ad_code . "\n\n" : '') . $banner_html;
		}

		return $ad_code;
	}

	protected function validate_ad_name($ad_name)
	{
		if ($ad_name === '')
		{
			$this->errors[] = $this->user->lang('AD_NAME_REQUIRED');
		}
		if (truncate_string($ad_name, self::MAX_NAME_LENGTH) !== $ad_name)
		{
			$this->errors[] = $this->user->lang('AD_NAME_TOO_LONG', self::MAX_NAME_LENGTH);
		}
	}

	protected function validate_ad_end_date($end_date)
	{
		if (preg_match('#^\d{4}\-\d{2}\-\d{2}$#', $end_date))
		{
			$end_date = (int) $this->end_date_to_timestamp($end_date);

			if ($end_date < time())
			{
				$this->errors[] = $this->user->lang('AD_END_DATE_INVALID');
			}
		}
		else if ($end_date !== '')
		{
			$this->errors[] = $this->user->lang('AD_END_DATE_INVALID');
		}
	}

	protected function validate_ad_priority($ad_priority)
	{
		if ($ad_priority < 1 || $ad_priority > 10)
		{
			$this->errors[] = $this->user->lang('AD_PRIORITY_INVALID');
		}
	}

	protected function validate_ad_views_limit($ad_views_limit)
	{
		if ($ad_views_limit < 0)
		{
			$this->errors[] = $this->user->lang('AD_VIEWS_LIMIT_INVALID');
		}
	}

	protected function validate_ad_clicks_limit($ad_clicks_limit)
	{
		if ($ad_clicks_limit < 0)
		{
			$this->errors[] = $this->user->lang('AD_CLICKS_LIMIT_INVALID');
		}
	}

	protected function validate_ad_owner($ad_owner)
	{
		// user_get_id_name function returns false if everything is OK.
		if (!empty($ad_owner) && user_get_id_name($ad_owner_id, $ad_owner))
		{
			$this->errors[] = $this->user->lang('AD_OWNER_INVALID');
		}
	}

	protected function end_date_to_timestamp($end_date)
	{
		return (int) $this->user->get_timestamp_from_format(controller::DATE_FORMAT, $end_date);
	}

	protected function owner_to_id($ad_owner)
	{
		if (empty($ad_owner))
		{
			return 0;
		}

		user_get_id_name($ad_owner_id, $ad_owner);
		return $ad_owner_id[0];
	}
}
