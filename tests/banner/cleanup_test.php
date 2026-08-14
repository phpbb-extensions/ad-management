<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\banner;

class cleanup_test extends banner_base
{
	/** @var string */
	protected $cleanup_root;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->cleanup_root = sys_get_temp_dir() . '/phpbb_ads_cleanup_' . md5(uniqid('', true)) . '/';
		mkdir($this->cleanup_root . 'images/phpbb_ads', 0777, true);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function tearDown(): void
	{
		foreach (glob($this->cleanup_root . 'images/phpbb_ads/*') as $file)
		{
			unlink($file);
		}
		rmdir($this->cleanup_root . 'images/phpbb_ads');
		rmdir($this->cleanup_root . 'images');
		rmdir($this->cleanup_root);

		parent::tearDown();
	}

	public function test_extract_filenames()
	{
		$first = str_repeat('a', 32) . '.jpg';
		$second = str_repeat('b', 32) . '.png';
		$code = '<img src="https://example.com/forum/images/phpbb_ads/' . $first . '">'
			. '<img src=&quot;/images/phpbb_ads/' . $second . '?v=1&quot;>'
			. '<img src="/images/phpbb_ads/not-managed.jpg">'
			. '<img src="/other/' . str_repeat('c', 32) . '.gif">'
			. '<img src="/images/phpbb_ads/' . $first . '">';

		$manager = new \phpbb\ads\banner\banner($this->files_upload, $this->filesystem, $this->cleanup_root);

		self::assertSame(array($first, $second), $manager->extract_filenames($code));
	}

	public function test_remove_only_unreferenced_managed_files()
	{
		$referenced = str_repeat('a', 32) . '.jpg';
		$orphan = str_repeat('b', 32) . '.png';
		$untracked = str_repeat('d', 32) . '.gif';
		file_put_contents($this->cleanup_root . 'images/phpbb_ads/' . $referenced, 'referenced');
		file_put_contents($this->cleanup_root . 'images/phpbb_ads/' . $orphan, 'orphan');
		file_put_contents($this->cleanup_root . 'images/phpbb_ads/' . $untracked, 'untracked');

		$this->filesystem->expects(self::once())
			->method('remove')
			->with($this->cleanup_root . 'images/phpbb_ads/' . $orphan);

		$manager = new \phpbb\ads\banner\banner($this->files_upload, $this->filesystem, $this->cleanup_root);
		$removed = $manager->remove_unreferenced(
			array($referenced, $orphan, '../../config.php', str_repeat('c', 32) . '.gif'),
			array('<img src="/images/phpbb_ads/' . $referenced . '">')
		);

		self::assertSame(array($orphan), $removed);
	}
}
