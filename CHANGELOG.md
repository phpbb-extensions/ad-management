# Changelog

### 2.0.6 - 2022-02-22

- Added an option for more aggressive handling of ad block users, requiring them to disable ad blockers to access the forum.
- Switched to a stronger ad block detection script based on BlockAdBlock.
- Add ADM pages to the non-content page restriction filter (to prevent showing ads during ACP login).
- Fix: Prevent session ID from blocking click and view tracking.

### 2.0.5 - 2021-06-15

- Updated and improved the appearance of the Visual Demo of Ad Locations.
- Updated and improved ad block detection.
- Improved (for hi-res displays) the appearance of the edit/delete icons in the ACP.
- Fixed various typos.

### 2.0.4 - 2021-01-21

- Feature: Added new advertisement locations before and after the Quick Reply editor.
- Fix: Title of the popup advertisement window will be properly displayed.
- Fix: Addressed potential PHP 8 compatibility issues.
- Fix: Correctly handle advertisement priority in MSSQL and Oracle databases.

### 2.0.3 - 2019-12-03

- Fix: Updated the "Display on content pages only" option so ads will no longer appear when writing posts or viewing member lists. This is to improve compliance with rules from Google AdSense.
- Fix: Addressed an issue where ad previews in the ACP could be hidden if your browser has Ad Blocking software.
- Fix: Addressed an issue when previewing an ad and any selected groups in the "Hide advertisement for groups" field would be lost.
- Fix: Minor code improvements and corrections.

### 2.0.2 - 2019-04-01

- Feature: Added a starting date option for advertisements.
- Fix: Addressed a caching issue related to users being moved in/out of groups and not seeing the correct ads immediately for their group status.

### 2.0.1 - 2018-09-17

- Feature: Added a new "Special" location we call "Scripts" which can be used for adding specialised Javascript codes like AdSense Auto ads and tracking codes.

### 2.0.0 - 2018-06-28

- Feature: Added an option for automatically centering an ad. Enable this option to have an ad be centered, or disable it and use your own positioning CSS in your ad code.
- Feature: Hiding ads from user groups is now set individually for each ad instead of globally. When upgrading from older versions, the old "Hide advertisement for groups" setting will be applied to all existing ads.
- Fix: Restyled the visual ad locations demo.
- Fix: Addressed minor code issues.

### 1.0.5 - 2018-03-06

- Feature: Added a new option to display ads on content pages only. When enabled, the ad will not be shown on the UCP, MCP, Registration and Profile pages. This will help comply with certain rules, such as Google AdSense which does not allow their ads to be shown on such pages.
- Feature: Ad locations option has improved organisation to make it easier to pick the appropriate location. Also, a new Visual Demo has been added, so you can see where all the ad locations physically appear on your board.
- Fix: My Advertisements tab in the UCP will no longer be shown to all users, and will only be visible to advertisement owners.
- Fix: Resolved an issue affecting some users where multiple ads assigned to the same location were not being randomly rotated.
- Fix: Ads assigned to display after first post will only be visible now if there are replies after the first post.

### 1.0.4 - 2017-12-23

- Fix: Improve handling of responsive advertisements.
- Fix: Added additional statistics to the "My advertisements" tab, including expiration info and active/inactive status.

### 1.0.3 - 2017-11-19

- Update: UCP Advertisements page shows more information about your ads including click/view limits, expiration date and active status of the ad.
- Fix: Improve handling of Google's responsive ads and possibly other responsive ad content. 

### 1.0.2 - 2017-08-20

- Feature: Added more advertisement placement locations, including interactive Pop-Up and Slide-Up types.
- Feature: Ad analyser can scan your Ad Code and test it for possibly dangerous or untrusted code.
- Fix: More precise ad view tracking will better ignore unwanted views from bots and crawlers.
- Fix: Renamed page routes should prevent ad-blockers from stopping ad view/click tracking.
- Fix: Solved an issue when previewing ad in Chrome is blocked by ERR_BLOCKED_BY_XSS_AUDITOR.
- Loads of code fixes and improvements.

### 1.0.1 - 2017-07-28

- Feature: Advertisement views and clicks statistics.
- Feature: Ad owner - a user can be designated as an ad owner and can view ad statistics.
- Feature: Banner image uploading added to the ACP advertisement creation form.
- Code fixes and improvements.

### 1.0.0 - 2017-06-24

- First release
