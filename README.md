# Advertisement Management

This is the repository for the development of the Advertisement Management extension, a Google Summer of Code project developed by Jakub Senko for phpBB.

[![Build Status](https://travis-ci.org/phpbb-extensions/ad-management.png)](https://travis-ci.org/phpbb-extensions/ad-management)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phpbb-extensions/ad-management/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phpbb-extensions/ad-management/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/phpbb-extensions/ad-management/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/phpbb-extensions/ad-management/?branch=master)

## Quick Install
You can install this on the latest release of phpBB 3.2 by following the steps below:
=======
The Advertisement Management extension allows phpBB board administrators to add and manage advertisements on their forums. Features include:

- Create unlimited advertisements. Accepts code snippets (such as Google AdSense) or create your own HTML/JS ads. Add, edit, preview, disable, delete and banner image upload functionality in the Admin Control Panel.
- Display ads in a variety of locations and techniques (inline, pop-up, slide-up).
- Set ad priority to display important ads more often than others.
- Statistics: ad views and clicks can be counted.
- Option to assign an ad owner who can monitor their ad’s statistics.
- Option to expire ads after reaching a certain date or number of clicks and/or views.
- Option to hide ads for specific member group(s).
- Ad-Blocker detection option politely notifies users to disable ad blocking on the board.
- Ad code analysis can check for troublesome code such as JS alert() or making HTTP requests from a secure server.

1. [Download the latest release](https://www.phpbb.com/customise/db/extension/ads/).
2. Unzip the downloaded release and copy it to the `ext` directory of your phpBB board.
3. Navigate in the ACP to `Customise -> Manage extensions`.
4. Look for `Advertisement Management` under the Disabled Extensions list, and click its `Enable` link.

## Uninstall

1. Navigate in the ACP to `Customise -> Extension Management -> Extensions`.
2. Look for `Advertisement Management` under the Enabled Extensions list, and click its `Disable` link.
3. To permanently uninstall, click `Delete Data` and then delete the `/ext/phpbb/ads` directory.

## Support

* **Important: Only official release versions validated by the phpBB Extensions Team should be installed on a live forum. Pre-release (alpha, beta, RC) versions downloaded from this repository are only to be used for testing on offline/development forums and are not officially supported.**
* Report bugs and other issues to our [Issue Tracker](https://github.com/phpbb-extensions/ad-management/issues).
* Support requests should be posted and discussed in the [Advertisement Management support forum at phpBB.com](https://www.phpbb.com/customise/db/extension/ads/support).

## Translations

* Translations should be posted to the [Advertisement Management topic at phpBB.com](https://www.phpbb.com/customise/db/extension/ads/support/topic/180776). We accept pull requests for translation corrections, but we do not accept pull requests for new translations.

## License
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)
