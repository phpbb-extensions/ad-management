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
	/** @var int Maximum ads accepted in one view batch */
	public const MAX_VIEW_BATCH = 50;

	/** @var int Click cooldown in seconds */
	public const CLICK_COOLDOWN = 10;

	/** @var \phpbb\ads\ad\manager */
	protected $manager;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param \phpbb\ads\ad\manager    $manager Advertisement manager object
	 * @param \phpbb\request\request                 $request Request object
	 * @param \phpbb\cache\driver\driver_interface  $cache Cache driver
	 * @param \phpbb\user                            $user User object
	 */
	public function __construct(\phpbb\ads\ad\manager $manager, \phpbb\request\request $request, \phpbb\cache\driver\driver_interface $cache, \phpbb\user $user)
	{
		$this->manager = $manager;
		$this->request = $request;
		$this->cache = $cache;
		$this->user = $user;
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
		if (!in_array($mode, array('clicks', 'views'), true) || !$this->is_valid_data($data, $mode))
		{
			throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED');
		}

		$link_name = $mode === 'views' ? 'phpbb_ads_views_' . $data : 'phpbb_ads_click_' . (int) $data;
		if (!$this->request->is_ajax()
			|| !check_link_hash($this->request->variable('hash', ''), $link_name))
		{
			throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED');
		}

		if ($mode !== 'clicks' || !$this->is_click_rate_limited($data))
		{
			$this->{$mode}($data);
		}

		return new \Symfony\Component\HttpFoundation\JsonResponse();
	}

	/**
	 * Validate route payload before using it.
	 *
	 * @param mixed $data Route payload
	 * @param string $mode Counter mode
	 * @return bool
	 */
	protected function is_valid_data($data, $mode)
	{
		if ($mode === 'clicks')
		{
			return ctype_digit((string) $data) && (int) $data > 0;
		}

		$ad_ids = explode('-', (string) $data);
		if (count($ad_ids) > self::MAX_VIEW_BATCH)
		{
			return false;
		}

		foreach ($ad_ids as $ad_id)
		{
			if (!ctype_digit($ad_id) || (int) $ad_id <= 0)
			{
				return false;
			}
		}

		return !empty($ad_ids);
	}

	/**
	 * Suppress rapid repeat clicks for same session and ad.
	 *
	 * @param int $ad_id Advertisement ID
	 * @return bool True when click should be suppressed
	 */
	protected function is_click_rate_limited($ad_id)
	{
		if (empty($this->user->session_id))
		{
			return false;
		}

		$key = '_phpbb_ads_click_' . hash('sha256', $this->user->session_id . ':' . (int) $ad_id);
		if ($this->cache->get($key) !== false)
		{
			return true;
		}

		$this->cache->put($key, true, self::CLICK_COOLDOWN);

		return false;
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
