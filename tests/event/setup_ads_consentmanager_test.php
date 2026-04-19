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

class setup_ads_consentmanager_test extends main_listener_base
{
	public function test_setup_ads_defers_ad_markup_when_consentmanager_is_enabled()
	{
		$stored_ad_code = htmlspecialchars(
			'<div class="ad-slot">Ad</div><script src="https://ads.example.com/tag.js"></script><iframe src="https://ads.example.com/frame"></iframe>',
			ENT_COMPAT
		);

		$this->user->data['user_id'] = 1;
		$this->user->page['page_name'] = 'index.' . $this->php_ext;
		$this->user->page['page_dir'] = '';

		$this->manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->setMethods(array('load_memberships', 'get_ads'))
			->getMock();
		$this->location_manager = $this->getMockBuilder('\phpbb\ads\location\manager')
			->disableOriginalConstructor()
			->setMethods(array('get_all_location_ids'))
			->getMock();

		$this->location_manager->expects(self::once())
			->method('get_all_location_ids')
			->willReturn(array('above_header'));

		$this->manager->expects(self::once())
			->method('load_memberships')
			->with(1)
			->willReturn(array());

		$this->manager->expects(self::once())
			->method('get_ads')
			->with(array('above_header'), array(), false)
			->willReturn(array(array(
				'location_id' => 'above_header',
				'ad_id' => 42,
				'ad_code' => $stored_ad_code,
				'ad_centering' => 0,
			)));

		$this->template->expects(self::exactly(2))
			->method('retrieve_var')
			->willReturnCallback(function ($var_name)
			{
				return $var_name === 'S_CONSENTMANAGER_MARKETING_ENABLED';
			});

		$this->template->expects(self::once())
			->method('assign_vars')
			->with(self::callback(function ($vars)
			{
				return $vars['AD_ABOVE_HEADER_ID'] === 42
					&& $vars['AD_ABOVE_HEADER_CENTER'] === false
					&& strpos($vars['AD_ABOVE_HEADER'], 'type="text/plain"') !== false
					&& strpos($vars['AD_ABOVE_HEADER'], 'data-consent-category="marketing"') !== false
					&& strpos($vars['AD_ABOVE_HEADER'], 'src="https://ads.example.com/tag.js"') !== false
					&& strpos($vars['AD_ABOVE_HEADER'], '<iframe src="https://ads.example.com/frame"></iframe>') !== false
					&& strpos($vars['AD_ABOVE_HEADER'], 'phpbb-ads-consent-placeholder') === false;
			}));

		$this->get_listener()->setup_ads();
	}

	public function test_setup_ads_adds_consent_category_to_text_plain_scripts()
	{
		$stored_ad_code = htmlspecialchars(
			'<script type="text/plain" src="https://ads.example.com/legacy.js"></script>',
			ENT_COMPAT
		);

		$this->user->data['user_id'] = 1;
		$this->user->page['page_name'] = 'index.' . $this->php_ext;
		$this->user->page['page_dir'] = '';

		$this->manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->setMethods(array('load_memberships', 'get_ads'))
			->getMock();
		$this->location_manager = $this->getMockBuilder('\phpbb\ads\location\manager')
			->disableOriginalConstructor()
			->setMethods(array('get_all_location_ids'))
			->getMock();

		$this->location_manager->expects(self::once())
			->method('get_all_location_ids')
			->willReturn(array('above_header'));

		$this->manager->expects(self::once())
			->method('load_memberships')
			->with(1)
			->willReturn(array());

		$this->manager->expects(self::once())
			->method('get_ads')
			->with(array('above_header'), array(), false)
			->willReturn(array(array(
				'location_id' => 'above_header',
				'ad_id' => 99,
				'ad_code' => $stored_ad_code,
				'ad_centering' => 0,
			)));

		$this->template->expects(self::exactly(2))
			->method('retrieve_var')
			->willReturnCallback(function ($var_name)
			{
				return $var_name === 'S_CONSENTMANAGER_MARKETING_ENABLED';
			});

		$this->template->expects(self::once())
			->method('assign_vars')
			->with(self::callback(function ($vars)
			{
				return $vars['AD_ABOVE_HEADER_ID'] === 99
					&& strpos($vars['AD_ABOVE_HEADER'], 'type="text/plain"') !== false
					&& strpos($vars['AD_ABOVE_HEADER'], 'data-consent-category="marketing"') !== false
					&& strpos($vars['AD_ABOVE_HEADER'], 'src="https://ads.example.com/legacy.js"') !== false;
			}));

		$this->get_listener()->setup_ads();
	}

	public function test_setup_ads_does_not_defer_when_marketing_category_is_disabled()
	{
		$stored_ad_code = htmlspecialchars(
			'<script src="https://ads.example.com/tag.js"></script>',
			ENT_COMPAT
		);

		$this->user->data['user_id'] = 1;
		$this->user->page['page_name'] = 'index.' . $this->php_ext;
		$this->user->page['page_dir'] = '';

		$this->manager = $this->getMockBuilder('\phpbb\ads\ad\manager')
			->disableOriginalConstructor()
			->setMethods(array('load_memberships', 'get_ads'))
			->getMock();
		$this->location_manager = $this->getMockBuilder('\phpbb\ads\location\manager')
			->disableOriginalConstructor()
			->setMethods(array('get_all_location_ids'))
			->getMock();

		$this->location_manager->expects(self::once())
			->method('get_all_location_ids')
			->willReturn(array('above_header'));

		$this->manager->expects(self::once())
			->method('load_memberships')
			->with(1)
			->willReturn(array());

		$this->manager->expects(self::once())
			->method('get_ads')
			->with(array('above_header'), array(), false)
			->willReturn(array(array(
				'location_id' => 'above_header',
				'ad_id' => 77,
				'ad_code' => $stored_ad_code,
				'ad_centering' => 0,
			)));

		$this->template->expects(self::exactly(2))
			->method('retrieve_var')
			->willReturn(false);

		$this->template->expects(self::once())
			->method('assign_vars')
			->with(self::callback(function ($vars)
			{
				return strpos($vars['AD_ABOVE_HEADER'], 'type="text/plain"') === false
					&& strpos($vars['AD_ABOVE_HEADER'], 'data-consent-category="marketing"') === false
					&& strpos($vars['AD_ABOVE_HEADER'], 'src="https://ads.example.com/tag.js"') !== false;
			}));

		$this->get_listener()->setup_ads();
	}
}
