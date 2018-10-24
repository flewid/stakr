=== Advanced Uptime Monitor Extension ===
Plugin Name: Advanced Uptime Monitor Extension
Plugin URI:
Description: MainWP Extension Plugin  for real-time up time monitoring.
Version: 2.1.1
Author: MainWP
Author URI:
Icon URI: http://extensions.mainwp.com/wp-content/uploads/2013/06/advanced-uptime-monitor-v2-150x150.png

== Description ==

Advanced Uptime Monitor Extension is all about helping you to keep your websites up.

It monitors your websites every 5 minutes and alerts you if your sites are down (actually, it is smarter, details below).


== Installation ==

1. Please install plugin "MainWP Dashboard" and active it before install Advanced Uptime Monitor Extension plugin( get the MainWP Dashboard plugin from url:http://mainwp.com/)  
2. Upload the `advanced-uptime-monitor-extension` folder to the `/wp-content/plugins/` directory
3. Activate the Advanced Uptime Monitor Extension plugin through the 'Plugins' menu in WordPress


== Screenshots ==

1. Display monitors on dashboard panel. 
2. Setting "Advanced Uptime Monitor Extension" in Extensions Menu with following functions:
     2.1. Set API Key for your Advanced Uptime Monitor.
     2.2. Set default alert contact for add new monitor.
     2.3. Display or hidden monitors on dashboard view panel.
     2.4. Multi delete monitor or delete monitor one by one. 
     2.5. Multi change status of monitors or change statusof monitor one by one 

== Changelog ==

= 2.1.1 = 10-9-2015
* Fixed: Bug with connecting to Uptime Robot
* Fixed: Various PHP Warnings

= 2.1.0 = 9-18-2015
* Updated: Refactored code to meet WordPress coding standards

= 2.0.0 = 8-18-2015
* Fixed: Compatibility issue with WordPress 4.3 version

= 1.9.9 = 8-13-2015
* Fixed: Bug with parsing JSON object
* Fixed: Bug with incorrect settings for Team Control extension

= 1.9.8 = 
* Fixed: Bug with montors disappearing in case Uptime Robot connection times out

= 1.9.7 = 
* Fixed: Bug where some values from the extension were not showing in the Client Reports extension scheduled reports

= 1.9.6 = 
* Fixed: Parsing incorrect json format issue
* Updated: Extension style

= 1.9.5 = 
* Updated: Quick start guide layout

= 1.9.4 =

* Fixed: Potential Security issue - Internal Code Audit

= 1.9.2 =

* Fixed: Bug where first monitor could not be added

= 1.9.1 =

* Fixed: PHP Warning

= 1.9 =

* Fixed: Bug when adding different monitor types for the same monitor
* Fixed: Bug when a monitor doesn't show in the list and it shows in Uptime Monitor API
* Added: "Reload Uptime Monitor Data" button
* Added: Support for the upcoming extension

= 1.8.99 = 

* Added: Support for 50+ monitors

= 1.8.98 =

* Added: Notification in case Uptime Robot doesn’t return data after adding a new monitor
* Added: Individual Dashboard widget shows monitor only for the child site

= 1.8.97 =

* Fixed: Fatal Error triggered by activating the extension
* Added: Additional plugin info

= 1.8.96 = 

* Added Help notice
* PHP Notice fixed
* Added redirection to the Extensions page after activating the extension

= 1.8.95 = 

* Update quick guide.

= 1.8.94 = 

* CSS Update.

= 1.8.93 = 

* Fix warning.

= 1.8.92 = 

* Fix warning.

= 1.8.91 = 

* Fix warning.

= 1.8.9 = 

* Fix Bug: php syntax and remove MVC header.

= 1.8.8 = 

* Fix Bug: css file not found

= 1.8.3 = 

* Fix Cosmetic Issue: Error Message Wording

= 1.8.2 = 

* Fix Bug: Unable to remove alert contact

= 1.8.1 = 

* Fix Extension Issues

= 1.8 = 

* Fix Cosmetic issue "Popup window needs to be moved a bit"

= 1.7 = 
* Fix Bug: Missing get alert contact which API Key contain only one contact. (cause: change from reponse of UptimeMonitor Server)

= 1.6 = 

* Fix Bug: Error Message in User's AUM Settings and Widget (cause : updated version PHP from 5.3 to 5.4)

= 1.5 = 

* Fix bug : Monitor Name displayed wrong.

= 1.4 = 

* Fix bug : update WordPress to version 3.6, and extension not working properly.

= 1.3 = 

* Fix bug "Set time out for loading monitors from server". After 20 second , return message can not connect to server 

= 1.2 = 
* Fix bug deprecated use reference for MVC Model.
* Fix bug "No permission to access files of plugin". Cause of this bug is rename folder of plugin when you upload not correctly.
* Fix bug "Changing/Adding API Key not working"  
* Fix issues about unable setting monitor or appear error when setting.
* Fix dashboard screen options issue
