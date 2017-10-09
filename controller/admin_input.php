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

use phpbb\ads\ext;

/**
 * Admin input
 */
class admin_input
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\ads\banner\banner */
	protected $banner;

	/** @var array Form validation errors */
	protected $errors = array();

	/**
	 * Constructor
	 *
	 * @param \phpbb\user              $user        User object
	 * @param \phpbb\user_loader       $user_loader User loader object
	 * @param \phpbb\language\language $language    Language object
	 * @param \phpbb\request\request   $request     Request object
	 * @param \phpbb\ads\banner\banner $banner      Banner upload object
	 */
	public function __construct(\phpbb\user $user, \phpbb\user_loader $user_loader, \phpbb\language\language $language, \phpbb\request\request $request, \phpbb\ads\banner\banner $banner)
	{
		$this->user = $user;
		$this->user_loader = $user_loader;
		$this->language = $language;
		$this->request = $request;
		$this->banner = $banner;

		add_form_key('phpbb_ads');
	}

	/**
	 * Gets all errors
	 *
	 * @return	array	Errors
	 */
	public function get_errors()
	{
		return $this->errors;
	}

	/**
	 * Returns number of errors.
	 *
	 * @return	int	Number of errors
	 */
	public function has_errors()
	{
		return count($this->errors);
	}

	/**
	 * Get admin form data.
	 *
	 * @return	array	Form data
	 */
	public function get_form_data()
	{
		$data = array(
			'ad_name'         => $this->request->variable('ad_name', '', true),
			'ad_note'         => $this->request->variable('ad_note', '', true),
			'ad_code'         => $this->request->variable('ad_code', '', true),
			'ad_enabled'      => $this->request->variable('ad_enabled', 0),
			'ad_locations'    => $this->request->variable('ad_locations', array('')),
			'ad_end_date'     => $this->request->variable('ad_end_date', ''),
			'ad_priority'     => $this->request->variable('ad_priority', ext::DEFAULT_PRIORITY),
			'ad_views_limit'  => $this->request->variable('ad_views_limit', 0),
			'ad_clicks_limit' => $this->request->variable('ad_clicks_limit', 0),
			'ad_owner'        => $this->request->variable('ad_owner', '', true),
		);

		// Validate form key
		if (!check_form_key('phpbb_ads'))
		{
			$this->errors[] = 'FORM_INVALID';
		}

		// Validate each property. Some validators update the property value. Errors are added to $this->errors.
		foreach ($data as $prop_name => $prop_val)
		{
			$method = 'validate_' . $prop_name;
			if (method_exists($this, $method))
			{
				$data[$prop_name] = $this->{$method}($prop_val);
			}
		}

		return $data;
	}

	/**
	 * Upload image and return updated ad code or <img> of new banner when using ajax.
	 *
	 * @param	 string	 $ad_code	 Current ad code
	 * @return	 string	 \phpbb\json_response when request is ajax or updated ad code otherwise.
	 */
	public function banner_upload($ad_code)
	{
		try
		{
			$this->banner->create_storage_dir();
			$realname = $this->banner->upload();

			$banner_html = '<img src="' . generate_board_url() . '/images/phpbb_ads/' . $realname . '" />';

			if ($this->request->is_ajax())
			{
				$this->send_ajax_response(true, $banner_html);
			}

			$ad_code = ($ad_code ? $ad_code . "\n\n" : '') . $banner_html;
		}
		catch (\phpbb\exception\runtime_exception $e)
		{
			$this->banner->remove();

			if ($this->request->is_ajax())
			{
				$this->send_ajax_response(false, $this->language->lang($e->getMessage()));
			}

			$this->errors[] = $this->language->lang($e->getMessage());
		}

		return $ad_code;
	}

	/**
	 * Validate advertisement name
	 *
	 * Ad name is required and must not be empty. Ad name must
	 * also be less than 255 characters.
	 *
	 * @param string $ad_name Advertisement name
	 * @return string Advertisement name
	 */
	protected function validate_ad_name($ad_name)
	{
		if ($ad_name === '')
		{
			$this->errors[] = 'AD_NAME_REQUIRED';
		}

		if (truncate_string($ad_name, ext::MAX_NAME_LENGTH) !== $ad_name)
		{
			$this->errors[] = $this->language->lang('AD_NAME_TOO_LONG', ext::MAX_NAME_LENGTH);
		}

		return $ad_name;
	}

	/**
	 * Validate advertisement code
	 *
	 * Ad code should not contain 4-byte Emoji characters.
	 *
	 * @param string $ad_code Advertisement code
	 * @return string Advertisement code
	 */
	protected function validate_ad_code($ad_code)
	{
		if (preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $ad_code, $matches))
		{
			$characters = implode(' ', $matches[0]);
			$this->errors[] = $this->language->lang('AD_CODE_ILLEGAL_CHARS', $characters);
		}

		return $ad_code;
	}

	/**
	 * Validate advertisement end date
	 *
	 * End date must use the expected format of YYYY-MM-DD.
	 * If the date is valid, convert it to a timestamp and then
	 * make sure the timestamp is less than the current time.
	 *
	 * @param string $end_date Advertisement end date
	 * @return int The end date converted to timestamp if valid, otherwise 0.
	 */
	protected function validate_ad_end_date($end_date)
	{
		$timestamp = 0;
		if (preg_match('#^\d{4}\-\d{2}\-\d{2}$#', $end_date))
		{
			$timestamp = (int) $this->user->get_timestamp_from_format(ext::DATE_FORMAT, $end_date);

			if ($timestamp < time())
			{
				$this->errors[] = 'AD_END_DATE_INVALID';
			}
		}
		else if ($end_date !== '')
		{
			$this->errors[] = 'AD_END_DATE_INVALID';
		}

		return $timestamp;
	}

	/**
	 * Validate advertisement priority
	 *
	 * Ad priority must be an integer between 1 and 10.
	 *
	 * @param int $ad_priority Advertisement priority
	 * @return int Advertisement priority
	 */
	protected function validate_ad_priority($ad_priority)
	{
		if ($ad_priority < 1 || $ad_priority > 10)
		{
			$this->errors[] = 'AD_PRIORITY_INVALID';
		}

		return $ad_priority;
	}

	/**
	 * Validate advertisement views limit
	 *
	 * Clicks must be a positive integer.
	 *
	 * @param int $ad_views_limit Advertisement views limit
	 * @return int Advertisement views limit
	 */
	protected function validate_ad_views_limit($ad_views_limit)
	{
		if ($ad_views_limit < 0)
		{
			$this->errors[] = 'AD_VIEWS_LIMIT_INVALID';
		}

		return $ad_views_limit;
	}

	/**
	 * Validate advertisement clicks limit
	 *
	 * Clicks must be a positive integer.
	 *
	 * @param int $ad_clicks_limit Advertisement clicks limit
	 * @return int Advertisement clicks limit
	 */
	protected function validate_ad_clicks_limit($ad_clicks_limit)
	{
		if ($ad_clicks_limit < 0)
		{
			$this->errors[] = 'AD_CLICKS_LIMIT_INVALID';
		}

		return $ad_clicks_limit;
	}

	/**
	 * Validate advertisement owner
	 *
	 * If ad owner name given, get their ID. If the ID returned is ANONYMOUS,
	 * set an error because the user name given doesn't exist.
	 *
	 * @param string $ad_owner User name
	 * @return int User id if user exists, otherwise 0.
	 */
	protected function validate_ad_owner($ad_owner)
	{
		$user_id = 0;
		if (!empty($ad_owner) && ANONYMOUS === ($user_id = (int) $this->user_loader->load_user_by_username($ad_owner)))
		{
			$this->errors[] = 'AD_OWNER_INVALID';
		}

		return ANONYMOUS !== $user_id ? $user_id : 0;
	}

	/**
	 * Send ajax response
	 *
	 * @param bool $success Is request successful?
	 * @param string $text Text to return
	 */
	protected function send_ajax_response($success, $text)
	{
		$json_response = new \phpbb\json_response;
		$json_response->send(array(
			'success'	=> $success,
			'title'		=> $this->language->lang('INFORMATION'),
			'text'		=> $text,
		));
	}
}
