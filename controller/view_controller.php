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
 * View controller
 */
class view_controller
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
	 * Increment views for ads
	 *
	 * @param	string	$ad_ids	Advertisement IDs
	 * @throws	\phpbb\exception\http_exception
	 * @return	\Symfony\Component\HttpFoundation\JsonResponse	A Symfony JsonResponse object
	 */
	public function increment_views($ad_ids)
	{
		if ($this->request->is_ajax())
		{
			$this->manager->increment_ads_views(explode('-', $ad_ids));

			return new \Symfony\Component\HttpFoundation\JsonResponse();
		}

		throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED');
	}
}
