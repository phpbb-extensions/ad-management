<?php
/**
 *
 * Advertisement management. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\ads\tests\event;

class main_listener_test extends main_listener_base
{
	/**
	* Test the event listener is constructed correctly
	*/
	public function test_construct()
	{
		$listener = $this->get_listener();
		self::assertInstanceOf('\Symfony\Component\EventDispatcher\EventSubscriberInterface', $listener);
	}

	/**
	* Test the event listener is subscribing events
	*/
	public function test_getSubscribedEvents()
	{
		self::assertEquals(array(
			'core.permissions',
			'core.user_setup',
			'core.page_footer_after',
			'core.page_header_after',
			'core.delete_user_after',
			'core.adm_page_header_after',
			'core.group_add_user_after',
			'core.group_delete_user_after',
			'phpbb.consentmanager.collect_registrations',
		), array_keys(\phpbb\ads\event\main_listener::getSubscribedEvents()));
	}

	public function test_register_ads()
	{
		$this->language->add_lang('common', 'phpbb/ads');
		$listener = $this->get_listener();
		$consent_manager = new consent_manager_double();

		$listener->register_ads(array(
			'consent_manager' => $consent_manager,
		));

		self::assertCount(1, $consent_manager->registrations);
		self::assertSame('phpbb.ads', $consent_manager->registrations[0]['id']);
		self::assertSame(array(
			'label' => $this->language->lang('PHPBB_ADS_CONSENT_LABEL'),
			'category' => \phpbb\ads\ad\manager::CONSENT_CATEGORY,
			'description' => $this->language->lang('PHPBB_ADS_CONSENT_DESCRIPTION'),
		), $consent_manager->registrations[0]['definition']);
	}
}

class consent_manager_double
{
	/** @var array */
	public $registrations = array();

	public function register($id, array $definition)
	{
		$this->registrations[] = array(
			'id' => $id,
			'definition' => $definition,
		);
	}
}
