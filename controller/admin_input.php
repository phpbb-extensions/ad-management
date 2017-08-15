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

/**
 * Admin input
 */
class admin_input
{
	const MAX_NAME_LENGTH = 255;
	const DATE_FORMAT = 'Y-m-d';
	const DEFAULT_PRIORITY = 5;

	/** @var \phpbb\user */
	protected $user;

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
	 * @param \phpbb\user								$user			User object
	 * @param \phpbb\language\language                  $language       Language object
	 * @param \phpbb\request\request					$request		Request object
	 * @param \phpbb\ads\banner\banner					$banner			Banner upload object
	 */
	public function __construct(\phpbb\user $user, \phpbb\language\language $language, \phpbb\request\request $request, \phpbb\ads\banner\banner $banner)
	{
		$this->user = $user;
		$this->language = $language;
		$this->request = $request;
		$this->banner = $banner;
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
	 * Add CSRF form key.
	 *
	 * @param	string	$form_name	The form name.
	 * @return	void
	 */
	public function add_form_key($form_name)
	{
		add_form_key($form_name);
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
			$this->errors[] = $this->language->lang('FORM_INVALID');
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
		if (!in_array('AD_OWNER_INVALID', $this->errors))
		{
			$data['ad_owner'] = $this->owner_to_id($data['ad_owner']);
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
	 * @param string $ad_name Advertisement name
	 */
	protected function validate_ad_name($ad_name)
	{
		if ($ad_name === '')
		{
			$this->errors[] = 'AD_NAME_REQUIRED';
		}
		if (truncate_string($ad_name, self::MAX_NAME_LENGTH) !== $ad_name)
		{
			$this->errors[] = $this->language->lang('AD_NAME_TOO_LONG', self::MAX_NAME_LENGTH);
		}
	}

	/**
	 * Validate advertisement end date
	 *
	 * @param string $end_date Advertisement end date
	 */
	protected function validate_ad_end_date($end_date)
	{
		if (preg_match('#^\d{4}\-\d{2}\-\d{2}$#', $end_date))
		{
			$end_date = (int) $this->end_date_to_timestamp($end_date);

			if ($end_date < time())
			{
				$this->errors[] = 'AD_END_DATE_INVALID';
			}
		}
		else if ($end_date !== '')
		{
			$this->errors[] = 'AD_END_DATE_INVALID';
		}
	}

	/**
	 * Validate advertisement priority
	 *
	 * @param int $ad_priority Advertisement priority
	 */
	protected function validate_ad_priority($ad_priority)
	{
		if ($ad_priority < 1 || $ad_priority > 10)
		{
			$this->errors[] = 'AD_PRIORITY_INVALID';
		}
	}

	/**
	 * Validate advertisement views limit
	 *
	 * @param int $ad_views_limit Advertisement views limit
	 */
	protected function validate_ad_views_limit($ad_views_limit)
	{
		if ($ad_views_limit < 0)
		{
			$this->errors[] = 'AD_VIEWS_LIMIT_INVALID';
		}
	}

	/**
	 * Validate advertisement clicks limit
	 *
	 * @param int $ad_clicks_limit Advertisement clicks limit
	 */
	protected function validate_ad_clicks_limit($ad_clicks_limit)
	{
		if ($ad_clicks_limit < 0)
		{
			$this->errors[] = 'AD_CLICKS_LIMIT_INVALID';
		}
	}

	/**
	 * Validate advertisement owner
	 *
	 * @param string $ad_owner Advertisement owner
	 */
	protected function validate_ad_owner($ad_owner)
	{
		// user_get_id_name function returns false if everything is OK.
		if (!empty($ad_owner) && user_get_id_name($ad_owner_id, $ad_owner))
		{
			$this->errors[] = 'AD_OWNER_INVALID';
		}
	}

	/**
	 * Convert format of end date from string to unix timestamp
	 *
	 * @param string $end_date Advertisement end date in YYYY-MM-DD format
	 * @return int Advertisement end date in unix timestamp
	 */
	protected function end_date_to_timestamp($end_date)
	{
		return (int) $this->user->get_timestamp_from_format(self::DATE_FORMAT, $end_date);
	}

	/**
	 * Convert advertisement owner username to ID
	 *
	 * @param string $ad_owner Advertisement owner username
	 * @return int Advertisement owner ID
	 */
	protected function owner_to_id($ad_owner)
	{
		if (empty($ad_owner))
		{
			return 0;
		}

		user_get_id_name($ad_owner_id, $ad_owner);
		return $ad_owner_id[0];
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
