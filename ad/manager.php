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
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

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
	 * @param    string                            $ads_table          Ads table
	 * @param    string                            $ad_locations_table Ad locations table
	 * @param    string                            $ad_group_table 	   Ad group table
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, $ads_table, $ad_locations_table, $ad_group_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->ads_table = $ads_table;
		$this->ad_locations_table = $ad_locations_table;
		$this->ad_group_table = $ad_group_table;
	}

	/**
	 * Get specific ad
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

		$sql = 'SELECT al.location_id, a.ad_id, a.ad_code, a.ad_centering
				FROM ' . $this->ad_locations_table . ' al
				LEFT JOIN ' . $this->ads_table . ' a
					ON (al.ad_id = a.ad_id)
				WHERE a.ad_enabled = 1
					AND (a.ad_start_date = 0
						OR a.ad_start_date < ' . time() . ')
					AND (a.ad_end_date = 0
						OR a.ad_end_date > ' . time() . ")
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
		$data = array_filter($data, function ($row) use (&$current_location_id) {
			$return = $current_location_id !== $row['location_id'];
			$current_location_id = $row['location_id'];
			return $return;
		});

		return $data;
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
	 * Note, that views are incremented only by one even when
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
	 * Increment clicks for specified ad
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
	 * Insert new advertisement to the database
	 *
	 * @param  array $data New ad data
	 * @return int        New advertisement ID
	 */
	public function insert_ad($data)
	{
		// extract ad groups here because it gets filtered in intersect_ad_data()
		$ad_groups = $data['ad_groups'];
		$data = $this->intersect_ad_data($data);

		// add a row to ads table
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
	 * Get all locations for specified advertisement
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
	 * Add rows to ad_group table.
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
	 * Remove all rows of specified ad in ad_group table
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
