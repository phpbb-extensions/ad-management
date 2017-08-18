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
* Increment controller
*/
class increment_controller
{
	/** @var \phpbb\ads\ad\manager */
	protected $manager;

	/** @var \phpbb\request\request */
	protected $request;

	/**
	 * Constructor
	 *
	 * @param \phpbb\ads\ad\manager    $manager Advertisement manager object
	 * @param \phpbb\request\request   $request	Request object
	 */
	public function __construct(\phpbb\ads\ad\manager $manager, \phpbb\request\request $request)
	{
		$this->manager = $manager;
		$this->request = $request;
	}

	/**
	 * Handle request.
	 *
	 * @param	mixed	$data	Ad ID or ad IDs
	 * @param	string	$mode	clicks or views
	 * @return	\Symfony\Component\HttpFoundation\JsonResponse	A Symfony JsonResponse object
	 * @throws	\phpbb\exception\http_exception
	 */
	public function handle($data, $mode)
	{
		if (!empty($data) && $this->request->is_ajax())
		{
			$this->{$mode}($data);

			return new \Symfony\Component\HttpFoundation\JsonResponse();
		}

		throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED');
	}

	/**
	 * Increment clicks for an ad.
	 *
	 * @param	int	$ad_id	Advertisement ID
	 */
	protected function clicks($ad_id)
	{
		$this->manager->increment_ad_clicks($ad_id);
	}

	/**
	 * Increment views for ads.
	 *
	 * @param	string	$ad_ids	Advertisement IDs
	 */
	protected function views($ad_ids)
	{
		$this->manager->increment_ads_views(explode('-', $ad_ids));
	}
}
