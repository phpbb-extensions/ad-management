<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads;

class ext extends \phpbb\extension\base
{
	const DATE_FORMAT = 'Y-m-d';
	const MAX_NAME_LENGTH = 255;
	const DEFAULT_PRIORITY = 5;
	const AD_BLOCK_MODES = [0, 1, 2];

	/**
	 * {@inheritdoc}
	 *
	 * Require phpBB 3.2.1 due to use of $event->update_subarray()
	 */
	public function is_enableable()
	{
		return phpbb_version_compare(PHPBB_VERSION, '3.2.1', '>=');
	}
}
