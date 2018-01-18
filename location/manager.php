<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\location;

class manager
{
	/**
	 * Array that contains all available template location types which are passed
	 * via the service container
	 * @var array
	 */
	protected $template_locations;

	/**
	 * Construct an template locations manager object
	 *
	 * @param	array	$template_locations	Template location types passed via the service container
	 */
	public function __construct($template_locations)
	{
		$this->register_template_locations($template_locations);
	}

	/**
	 * Get a list of all template location types
	 *
	 * If $with_categories is true, returns a composite associated array
	 * of location category, ID, name and desc:
	 * array(
	 *    location_category => array(
	 *       location_id => array(
	 *          'name' => location_name
	 *          'desc' => location_description
	 *       ),
	 *       ...
	 *    ),
	 *    ...
	 * )
	 *
	 * Otherwise returns only location ID, name and desc:
	 * array(
	 *    location_id => array(
	 *       'name' => location_name
	 *       'desc' => location_description
	 *    ),
	 *    ...
	 * )
	 *
	 * @param	bool	$with_categories	Should we organize locations into categories?
	 *
	 * @return	array	Array containing a list of all template locations sorted by categories
	 */
	public function get_all_locations($with_categories = true)
	{
		$location_types = array();

		foreach ($this->template_locations as $location_category_id => $location_category)
		{
			foreach ($location_category as $id => $location_type)
			{
				$body = array(
					'name'	=> $location_type->get_name(),
					'desc'	=> $location_type->get_desc(),
				);

				if ($with_categories)
				{
					$location_types[$location_category_id][$id] = $body;
				}
				else
				{
					$location_types[$id] = $body;
				}
			}
		}

		return $location_types;
	}

	/**
	 * Get a list of all template location IDs for display
	 *
	 * @return	array	Array containing a list of all template location IDs
	 */
	public function get_all_location_ids()
	{
		$template_locations = array();

		foreach ($this->template_locations as $location_category)
		{
			foreach ($location_category as $location_id => $location)
			{
				if ($location->will_display())
				{
					$template_locations[] = $location_id;
				}
			}
		}

		return $template_locations;
	}

	/**
	 * Register template locations
	 *
	 * @param	array	$template_locations	Template location types passed via the service container
	 */
	protected function register_template_locations($template_locations)
	{
		if (!empty($template_locations))
		{
			// Define categories here for custom ordering.
			// Static definition also prevents external location
			// types to use nondefined category.
			$this->template_locations = array(
				'CAT_TOP_OF_PAGE'		=> array(),
				'CAT_BOTTOM_OF_PAGE'	=> array(),
				'CAT_IN_POSTS'			=> array(),
				'CAT_OTHER'				=> array(),
				'CAT_INTERACTIVE'		=> array(),
			);

			foreach ($template_locations as $location)
			{
				$this->template_locations[$location->get_category()][$location->get_id()] = $location;
			}
		}
	}
}
