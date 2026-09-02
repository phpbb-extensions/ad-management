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
	/** @var int Tracking cooldown in seconds */
	public const TRACKING_COOLDOWN = 10;
	/** @var int Number of tracking lock files used to limit contention */
	public const TRACKING_LOCK_BUCKETS = 64;

	/** @var \phpbb\ads\ad\manager */
	protected $manager;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $cache_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\ads\ad\manager                  $manager Advertisement manager object
	 * @param \phpbb\request\request                 $request Request object
	 * @param \phpbb\cache\driver\driver_interface  $cache Cache driver
	 * @param \phpbb\user                            $user User object
	 * @param string                                   $cache_path Cache directory
	 */
	public function __construct(\phpbb\ads\ad\manager $manager, \phpbb\request\request $request, \phpbb\cache\driver\driver_interface $cache, \phpbb\user $user, $cache_path)
	{
		$this->manager = $manager;
		$this->request = $request;
		$this->cache = $cache;
		$this->user = $user;
		$this->cache_path = rtrim($cache_path, '/\\') . '/';
	}

	/**
	 * Handle request.
	 *
	 * @param	mixed	$data	Ad ID
	 * @param	string	$mode	clicks or views
	 * @return	\Symfony\Component\HttpFoundation\JsonResponse	A Symfony JsonResponse object
	 * @throws	\phpbb\exception\http_exception
	 */
	public function handle($data, $mode)
	{
		if (!in_array($mode, array('clicks', 'views'), true) || !$this->is_valid_data($data))
		{
			throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED');
		}

		$link_name = $mode === 'views' ? 'phpbb_ads_views_' . $data : 'phpbb_ads_click_' . (int) $data;
		if (!$this->request->is_ajax()
			|| !check_link_hash($this->request->variable('hash', ''), $link_name))
		{
			throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED');
		}

		$ad_id = (int) $data;
		if (!$this->is_rate_limited($mode, $ad_id))
		{
			$this->{$mode}($ad_id);
		}

		return new \Symfony\Component\HttpFoundation\JsonResponse();
	}

	/**
	 * Validate route payload before using it.
	 *
	 * @param mixed $data Route payload
	 * @return bool
	 */
	protected function is_valid_data($data)
	{
		return ctype_digit((string) $data) && (int) $data > 0;
	}

	/**
	 * Suppress rapid repeat tracking requests for same IP, mode, and ad.
	 *
	 * @param string $mode Counter mode
	 * @param int    $ad_id Advertisement ID
	 * @return bool True when request should be suppressed
	 */
	protected function is_rate_limited($mode, $ad_id)
	{
		if (empty($this->user->ip))
		{
			return true;
		}

		$key_hash = hash('sha256', $mode . ':' . $this->user->ip . ':' . (int) $ad_id);
		$key = '_phpbb_ads_tracking_' . $key_hash;
		$lock = $this->get_tracking_lock($key_hash);
		try
		{
			$lock_acquired = $lock->acquire();
		}
		catch (\phpbb\exception\http_exception $e)
		{
			return true;
		}

		if (!$lock_acquired)
		{
			return true;
		}

		try
		{
			if ($this->cache->get($key) !== false)
			{
				return true;
			}

			$this->cache->put($key, true, self::TRACKING_COOLDOWN);
		}
		finally
		{
			$lock->release();
		}

		return false;
	}

	/**
	 * Get a striped lock for an atomic cooldown cache check and write.
	 *
	 * @param string $key_hash Tracking cache key hash
	 * @return \phpbb\lock\flock
	 */
	protected function get_tracking_lock($key_hash)
	{
		$bucket = hexdec(substr($key_hash, 0, 2)) % self::TRACKING_LOCK_BUCKETS;

		return new \phpbb\lock\flock($this->cache_path . 'phpbb_ads_tracking_' . $bucket);
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
	 * Increment views for an ad.
	 *
	 * @param	int	$ad_id	Advertisement ID
	 */
	protected function views($ad_id)
	{
		$this->manager->increment_ad_views($ad_id);
	}
}
