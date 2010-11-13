Introduction
------------

This deals with the Yahoo Fantasy Sports API. 

Currently its main task deals with message boards. There is a 100 message limit at
Yahoo, meaning all messages are deleted except the last 100, so the intent of this
application is to archive the messages for later use. And eventually add useful
features like search.

Also note that a users (your) Yahoo username and password are not
saved nor seen by this application, and instead an OAuth token is
used which provides temporary access to some information, like
Fantasy Sports message board posts.

Requirements
------------

* PHP 5
* PHP Extensions: simplexml (default), PDO (default), PDO SQLite driver* (default), and OAuth (in PECL, not standard)
* Optionally edit to use a non-sqlite driver, as any PDO friendly DB should work (all queries are simple)
* Yahoo API Key: <https://developer.apps.yahoo.com/dashboard/createKey.html>
* setup.php checks for all of these requirements, so it'll yell at you if not ready
* Note: It's rare for a host to have or enable the OAuth extension.

Installation
------------

* Place all files in a web accessible directory
* Determine where the sqlite database will be loaded
* Modify inc/config.inc.php according to your setup
* Run setup.php
* Setup will either state problems or say installation was a success

Usage
------------

* Run authenticate.php to request and save a Yahoo Authentication token
* Run myinfo.php to view some information about yourself
* Run messages.php to archive and view remote and local message board posts
* Run rest.php to execute arbitrary REST commands
* Run transactions.php to archive and view remote and local transactions

Notes
------------

* Initially this project was named YahooFantasyMessages and only dealt with message posts
* This is not well tested and does contain bugs
* Please find and fix bugs, and cleanup code
* The functionality is simple, and not pretty (e.g., ugly HTML) but it works
* Revoking OAuth tokens for Yahoo applications can be done here: <https://api.login.yahoo.com/WSLogin/V1/unlink>

TODO
------------

* Find and report bugs, and fix them
* Add transaction archiving (although these are saved forever (i.e., no 100 limit))
* Add prettier output instead of print_r() everywhere
* Test on other setups, and as other users
* Make it multi-user friendly, and authentication in general
* Allow non-sqlite PDO drivers to easily work
* Don't refresh the token so often (near every request)
* Make less rest calls by combining queries (and do more caching/local storage)
* Add data export options, like to CSV/Excel
* Add tools for local copies, like full-text search (or similar) 
* Allow non pecl oauth extension users to utilize this, by optionally allowing the Yahoo oauth wrappers
* Add documentation
* Clean leftover code that's specific to the old YahooFantasyMessages project

License
------------

* All files here use the Apache 2.0 license: <http://www.apache.org/licenses/LICENSE-2.0.html>
* Author: Philip Olson <philip@roshambo.org>
