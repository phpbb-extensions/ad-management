<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\ad;

class manager
{
	public const CONSENT_CATEGORY = 'marketing';

	/**
	 * Google ad/tag scripts that support Google Consent Mode.
	 *
	 * These should run immediately so Consent Mode can control storage and
	 * personalization instead of blocking the ad tag entirely.
	 */
	protected const GOOGLE_CONSENT_AWARE_SCRIPT_SOURCE_PATTERNS = array(
		'~(^|[/.])pagead2\.googlesyndication\.com/pagead/js/adsbygoogle\.js(?:[?#]|$)~i',
		'~(^|[/.])securepubads\.g\.doubleclick\.net/tag/js/gpt\.js(?:[?#]|$)~i',
		'~(^|[/.])www\.googletagservices\.com/tag/js/gpt\.js(?:[?#]|$)~i',
		'~(^|[/.])www\.googletagmanager\.com/(?:gtag/js|gtm\.js)(?:[?#]|$)~i',
	);

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $ads_table;

	/** @var string */
	protected $ad_locations_table;

	/** @var string */
	protected $ad_group_table;

	/**
	 * Constructor
	 *
	 * @param    \phpbb\db\driver\driver_interface $db                 DB driver interface
	 * @param    \phpbb\config\config              $config             Config object
	 * @param    \phpbb\user                       $user               User object
	 * @param    string                            $ads_table          Ads table
	 * @param    string                            $ad_locations_table Ad locations table
	 * @param    string                            $ad_group_table 	   Ad group table
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\user $user, $ads_table, $ad_locations_table, $ad_group_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
		$this->ads_table = $ads_table;
		$this->ad_locations_table = $ad_locations_table;
		$this->ad_group_table = $ad_group_table;
	}

	/**
	 * Get a specific ad
	 *
	 * @param	int	$ad_id	Advertisement ID
	 * @return	array	Array with advertisement data
	 */
	public function get_ad($ad_id)
	{
		$sql = 'SELECT *
			FROM ' . $this->ads_table . '
			WHERE ad_id = ' . (int) $ad_id;
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $data !== false ? $data : [];
	}

	/**
	 * Get one ad per every location
	 *
	 * @param    array $ad_locations List of ad locations to fetch ads for
	 * @param    array $user_groups List of user groups
	 * @param    bool  $non_content_page Is current page non-content oriented (e.g.: login, UCP, MCP)? Default is false.
	 * @return    array    List of ad codes for each location
	 */
	public function get_ads($ad_locations, $user_groups, $non_content_page = false)
	{
		$sql_where_views = $this->config['phpbb_ads_enable_views'] ? 'AND (a.ad_views_limit = 0 OR a.ad_views_limit > a.ad_views)' : '';
		$sql_where_clicks = $this->config['phpbb_ads_enable_clicks'] ? 'AND (a.ad_clicks_limit = 0 OR a.ad_clicks_limit > a.ad_clicks)' : '';
		$sql_where_non_content = $non_content_page ? 'AND a.ad_content_only = 0' : '';
		$sql_where_user_groups = !empty($user_groups) ? 'AND NOT EXISTS (SELECT ag.group_id FROM ' . $this->ad_group_table . ' ag WHERE ag.ad_id = a.ad_id AND ' . $this->db->sql_in_set('ag.group_id', $user_groups) . ')' : '';

		// Get user's current time and convert to UTC equivalent for comparison
		$user_now = $this->user->create_datetime();
		$sql_time = (int) gmmktime(
			(int) $user_now->format('H'),
			(int) $user_now->format('i'),
			(int) $user_now->format('s'),
			(int) $user_now->format('m'),
			(int) $user_now->format('d'),
			(int) $user_now->format('Y')
		);

		$sql = 'SELECT al.location_id, a.ad_id, a.ad_code, a.ad_centering, a.ad_consent
				FROM ' . $this->ad_locations_table . ' al
				LEFT JOIN ' . $this->ads_table . ' a
					ON (al.ad_id = a.ad_id)
				WHERE a.ad_enabled = 1
					AND (a.ad_start_date = 0
						OR a.ad_start_date <= ' . $sql_time . ')
					AND (a.ad_end_date = 0
						OR a.ad_end_date > ' . $sql_time . ")
					$sql_where_views
					$sql_where_clicks
					$sql_where_non_content
					$sql_where_user_groups
					AND " . $this->db->sql_in_set('al.location_id', $ad_locations) . '
				ORDER BY al.location_id, (' . $this->sql_random() . ' * a.ad_priority) DESC';
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		if (empty($data))
		{
			return [];
		}

		$current_location_id = '';
		return array_filter($data, static function ($row) use (&$current_location_id) {
			$return = $current_location_id !== $row['location_id'];
			$current_location_id = $row['location_id'];
			return $return;
		});
	}

	/**
	 * Get all advertisements.
	 *
	 * @return    array    List of all ads
	 */
	public function get_all_ads()
	{
		$sql = 'SELECT ad_id, ad_priority, ad_name, ad_enabled, ad_start_date, ad_end_date, ad_views, ad_clicks, ad_views_limit, ad_clicks_limit
			FROM ' . $this->ads_table;
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $data;
	}

	/**
	 * Get all owner's ads
	 *
	 * @param    int $user_id Ad owner
	 * @return    array    List of owner's ads
	 */
	public function get_ads_by_owner($user_id)
	{
		$sql = 'SELECT ad_id, ad_name, ad_enabled, ad_start_date, ad_end_date, ad_views, ad_views_limit, ad_clicks, ad_clicks_limit
			FROM ' . $this->ads_table . '
			WHERE ad_owner = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $data;
	}

	/**
	 * Increment views for specified ads
	 *
	 * Note that views are incremented only by one even when
	 * an ad is displayed multiple times on the same page.
	 *
	 * @param    array $ad_ids IDs of ads to increment views
	 * @return    void
	 */
	public function increment_ads_views($ad_ids)
	{
		if (!empty($ad_ids))
		{
			$sql = 'UPDATE ' . $this->ads_table . '
				SET ad_views = ad_views + 1
				WHERE ' . $this->db->sql_in_set('ad_id', $ad_ids);
			$this->db->sql_query($sql);
		}
	}

	/**
	 * Increment clicks for a specified ad
	 *
	 * @param    int $ad_id ID of an ad to increment clicks
	 * @return    void
	 */
	public function increment_ad_clicks($ad_id)
	{
		$sql = 'UPDATE ' . $this->ads_table . '
			SET ad_clicks = ad_clicks + 1
			WHERE ad_id = ' . (int) $ad_id;
		$this->db->sql_query($sql);
	}

	/**
	 * Insert a new advertisement to the database
	 *
	 * @param  array $data New ad data
	 * @return int        New advertisement ID
	 */
	public function insert_ad($data)
	{
		// extract ad groups here because it gets filtered in intersect_ad_data()
		$ad_groups = $data['ad_groups'];
		$data = $this->intersect_ad_data($data);

		// add a row to the ad table
		$sql = 'INSERT INTO ' . $this->ads_table . ' ' . $this->db->sql_build_array('INSERT', $data);
		$this->db->sql_query($sql);
		$ad_id = (int) $this->db->sql_nextid();

		$this->insert_ad_group_data($ad_id, $ad_groups);

		return $ad_id;
	}

	/**
	 * Update advertisement
	 *
	 * @param    int   $ad_id Advertisement ID
	 * @param    array $data  List of data to update in the database
	 * @return    int        Number of affected rows. Can be used to determine if any ad has been updated.
	 */
	public function update_ad($ad_id, $data)
	{
		// extract ad groups here because it gets filtered in intersect_ad_data()
		$ad_groups = $data['ad_groups'] ?? [];
		$data = $this->intersect_ad_data($data);

		$sql = 'UPDATE ' . $this->ads_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $data) . '
			WHERE ad_id = ' . (int) $ad_id;
		$this->db->sql_query($sql);
		$result = $this->db->sql_affectedrows();

		$this->remove_ad_group_data($ad_id);
		$this->insert_ad_group_data($ad_id, $ad_groups);

		return $result;
	}

	/**
	 * Delete advertisement
	 *
	 * @param    int $ad_id Advertisement ID
	 * @return    int        Number of affected rows. Can be used to determine if any ad has been deleted.
	 */
	public function delete_ad($ad_id)
	{
		$sql = 'DELETE FROM ' . $this->ads_table . '
			WHERE ad_id = ' . (int) $ad_id;
		$this->db->sql_query($sql);

		return $this->db->sql_affectedrows();
	}

	/**
	 * Remove ad owner
	 *
	 * @param    array $user_ids User IDs
	 * @return    void
	 */
	public function remove_ad_owner(array $user_ids)
	{
		if (empty($user_ids))
		{
			return;
		}

		$sql = 'UPDATE ' . $this->ads_table . '
			SET ad_owner = 0
			WHERE ' . $this->db->sql_in_set('ad_owner', $user_ids);
		$this->db->sql_query($sql);
	}

	/**
	 * Get all locations for a specified advertisement
	 *
	 * @param	int		$ad_id	Advertisement ID
	 * @return	array	List of template locations for specified ad
	 */
	public function get_ad_locations($ad_id)
	{
		$ad_locations = [];

		$sql = 'SELECT location_id
			FROM ' . $this->ad_locations_table . '
			WHERE ad_id = ' . (int) $ad_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ad_locations[] = $row['location_id'];
		}
		$this->db->sql_freeresult($result);

		return $ad_locations;
	}

	/**
	 * Insert advertisement locations
	 *
	 * @param	int		$ad_id			Advertisement ID
	 * @param	array	$ad_locations	List of template locations for this ad
	 * @return	void
	 */
	public function insert_ad_locations($ad_id, $ad_locations)
	{
		$sql_ary = [];
		foreach ($ad_locations as $ad_location)
		{
			$sql_ary[] = [
				'ad_id'			=> $ad_id,
				'location_id'	=> $ad_location,
			];
		}
		$this->db->sql_multi_insert($this->ad_locations_table, $sql_ary);
	}

	/**
	 * Delete advertisement locations
	 *
	 * @param	int		$ad_id	Advertisement ID
	 * @return	void
	 */
	public function delete_ad_locations($ad_id)
	{
		$sql = 'DELETE FROM ' . $this->ad_locations_table . '
			WHERE ad_id = ' . (int) $ad_id;
		$this->db->sql_query($sql);
	}

	/**
	 * Load memberships of the user
	 *
	 * @param	int		$user_id	User ID to load memberships
	 * @return	array	List of group IDs user is member of
	 */
	public function load_memberships($user_id)
	{
		$memberships = [];
		$sql = 'SELECT group_id
			FROM ' . USER_GROUP_TABLE . '
			WHERE user_id = ' . (int) $user_id . '
			AND user_pending = 0';
		$result = $this->db->sql_query($sql, 3600);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$memberships[] = $row['group_id'];
		}
		$this->db->sql_freeresult($result);
		return $memberships;
	}

	/**
	 * Load all board groups
	 *
	 * @param	int		$ad_id	Advertisement ID
	 * @return	array	List of groups
	 */
	public function load_groups($ad_id)
	{
		$sql = 'SELECT g.group_id, g.group_name, (
				SELECT COUNT(ad_id)
				FROM ' . $this->ad_group_table . ' ag
				WHERE ag.ad_id = ' . (int) $ad_id . '
					AND ag.group_id = g.group_id
			) as group_selected
			FROM ' . GROUPS_TABLE . " g
			WHERE g.group_name <> 'BOTS'
			ORDER BY g.group_name ASC";
		$result = $this->db->sql_query($sql);
		$groups = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $groups;
	}

	/**
	 * Prepare ad code for output, applying consent-manager deferrals when enabled.
	 *
	 * @param string $ad_code         Stored advertisement code
	 * @param bool   $consent_enabled Whether marketing consent is required
	 * @return string
	 */
	public function prepare_ad_code($ad_code, $consent_enabled)
	{
		$ad_code = htmlspecialchars_decode($ad_code, ENT_COMPAT);
		$original_ad_code = $ad_code;

		if (!$consent_enabled || $ad_code === '')
		{
			return $ad_code;
		}

		$google_consent_aware_sources = self::get_google_consent_aware_script_sources($ad_code);

		$ad_code = preg_replace_callback('#<script\b([^>]*)>(.*?)</script\s*>#is', function ($matches) use ($google_consent_aware_sources)
		{
			$attributes = $matches[1] ?? '';
			$content = $matches[2] ?? '';

			if (!$this->should_defer_script_tag($attributes, $content, $google_consent_aware_sources))
			{
				return $matches[0];
			}

			return '<script' . $this->inject_consent_attributes($attributes) . '>' . $content . '</script>';
		}, $ad_code);

		return $ad_code ?? $original_ad_code;
	}

	/**
	 * Determine whether a script tag is executable and should be deferred.
	 *
	 * @param string $attributes Script tag attributes
	 * @param string $content Script tag content
	 * @param array $google_consent_aware_sources Known Google loader sources in this ad block
	 * @return bool
	 */
	protected function should_defer_script_tag($attributes, $content = '', array $google_consent_aware_sources = array())
	{
		if (preg_match('/\bdata-consent-category\s*=/i', $attributes))
		{
			return false;
		}

		if (preg_match('/\btype\s*=\s*([\'"])(.*?)\1/i', $attributes, $matches))
		{
			$type = strtolower(trim(explode(';', $matches[2])[0]));
		}
		else
		{
			$type = '';
		}

		$is_executable = $type === ''
			|| $type === 'text/plain'
			|| $type === 'module'
			|| strpos($type, 'javascript') !== false
			|| strpos($type, 'ecmascript') !== false;

		if (!$is_executable)
		{
			return false;
		}

		return !self::is_google_consent_aware_script($attributes, $content, $google_consent_aware_sources);
	}

	/**
	 * Determine whether a script should run under Google Consent Mode.
	 *
	 * @param string $attributes Script tag attributes
	 * @param string $content Script tag content
	 * @param array $google_consent_aware_sources Known Google loader sources in this ad block
	 * @return bool
	 */
	public static function is_google_consent_aware_script($attributes, $content, array $google_consent_aware_sources)
	{
		$source = self::extract_script_source($attributes);
		if ($source !== '')
		{
			return isset($google_consent_aware_sources[self::normalize_script_source($source)]);
		}

		return !empty($google_consent_aware_sources)
			&& preg_match('/\b(?:adsbygoogle|googletag|gtag|dataLayer)\b/', $content);
	}

	/**
	 * Return known Google Consent Mode-aware loader sources in an ad block.
	 *
	 * @param string $ad_code Advertisement code
	 * @return array
	 */
	public static function get_google_consent_aware_script_sources($ad_code)
	{
		$sources = array();

		if (!preg_match_all('#<script\b([^>]*)>#i', $ad_code, $matches))
		{
			return $sources;
		}

		foreach ($matches[1] as $attributes)
		{
			$source = self::extract_script_source($attributes);
			if ($source !== '' && self::is_google_consent_aware_script_source($source))
			{
				$sources[self::normalize_script_source($source)] = true;
			}
		}

		return $sources;
	}

	/**
	 * Extract the src attribute from a script tag attribute string.
	 *
	 * @param string $attributes Script tag attributes
	 * @return string
	 */
	public static function extract_script_source($attributes)
	{
		return preg_match('/\bsrc\s*=\s*([\'"])(.*?)\1/i', $attributes, $matches) ? $matches[2] : '';
	}

	/**
	 * Check whether a script source is a known Google Consent Mode-aware loader.
	 *
	 * @param string $source Script source URL
	 * @return bool
	 */
	protected static function is_google_consent_aware_script_source($source)
	{
		$source = self::normalize_script_source($source);

		foreach (self::GOOGLE_CONSENT_AWARE_SCRIPT_SOURCE_PATTERNS as $pattern)
		{
			if (preg_match($pattern, $source))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Normalize a script source before comparing against allowlisted loaders.
	 *
	 * @param string $source Script source URL
	 * @return string
	 */
	protected static function normalize_script_source($source)
	{
		return preg_replace('#^//#', 'https://', trim($source));
	}

	/**
	 * Replace script tag attributes with consent-aware placeholders.
	 *
	 * @param string $attributes Script tag attributes
	 * @return string
	 */
	protected function inject_consent_attributes($attributes)
	{
		if (preg_match('/\btype\s*=\s*([\'"])(.*?)\1/i', $attributes))
		{
			$attributes = preg_replace('/\btype\s*=\s*([\'"])(.*?)\1/i', 'type="text/plain"', $attributes, 1);
		}
		else
		{
			$attributes .= ' type="text/plain"';
		}

		if (!preg_match('/\bdata-consent-category\s*=/i', $attributes))
		{
			$attributes .= ' data-consent-category="' . self::CONSENT_CATEGORY . '"';
		}

		return $attributes;
	}

	/**
	 * Make sure only necessary data make their way to SQL query
	 *
	 * @param	array	$data	List of data to query the database
	 * @return	array	Cleaned data that contain only valid keys
	 */
	protected function intersect_ad_data($data)
	{
		return array_intersect_key($data, [
			'ad_name'			=> '',
			'ad_note'			=> '',
			'ad_code'			=> '',
			'ad_enabled'		=> '',
			'ad_start_date'		=> '',
			'ad_end_date'		=> '',
			'ad_priority'		=> '',
			'ad_views_limit'	=> '',
			'ad_clicks_limit'	=> '',
			'ad_owner'			=> '',
			'ad_content_only'	=> '',
			'ad_centering'		=> '',
			'ad_consent'		=> '',
		]);
	}

	/**
	 * Get the random statement for this database layer
	 * Random function should generate a float value between 0 and 1
	 *
	 * @return	string	Random statement for current database layer
	 */
	protected function sql_random()
	{
		switch ($this->db->get_sql_layer())
		{
			case 'oracle':
				return 'DBMS_RANDOM.VALUE';

			case 'postgres':
				return 'RANDOM()';

			// https://stackoverflow.com/a/35369410/2908600
			case 'sqlite':
			case 'sqlite3':
				return '(0.5 - RANDOM() / CAST(-9223372036854775808 AS REAL) / 2)';

			// https://improve.dk/weighted-random-selections-in-sql-server/
			case 'mssql':
			case 'mssql_odbc':
			case 'mssqlnative':
				return 'RAND(CAST(NEWID() AS VARBINARY))';

			default:
				return 'RAND()';
		}
	}

	/**
	 * Add rows to the ad_group table.
	 *
	 * @param int   $ad_id     Advertisement ID
	 * @param array $ad_groups List of groups that should not see this ad
	 * @return void
	 */
	protected function insert_ad_group_data($ad_id, $ad_groups)
	{
		$sql_ary = [];
		foreach ($ad_groups as $group)
		{
			$sql_ary[] = [
				'ad_id'		=> $ad_id,
				'group_id'	=> $group,
			];
		}
		$this->db->sql_multi_insert($this->ad_group_table, $sql_ary);
	}

	/**
	 * Remove all rows of the specified ad in the ad_group table
	 *
	 * @param int	$ad_id	Advertisement ID
	 * @return void
	 */
	protected function remove_ad_group_data($ad_id)
	{
		$sql = 'DELETE FROM ' . $this->ad_group_table . '
			WHERE ad_id = ' . (int) $ad_id;
		$this->db->sql_query($sql);
	}
}
