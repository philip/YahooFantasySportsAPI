Status:
------------
Do not use this. 

It was created 10 or so years ago for a different purpose, is unsightly, and no longer works.

Trust me, do not use or look at this.

I'm not kidding.

I'm relearning to program after years off so will rewrite it eventually. Maybe. 


Introduction
------------

This deals with the Yahoo Fantasy Sports API.

Originally its main task was to archive message boards, which has/had a 100
message limit. Meaning, Yahoo deleted (forever) all messages except the last
100 so this archived them. This is no longer a use case.

This authenticates against your Yahoo login using an OAuth token, so this
system does not have access to your username / password.

From here you can also execute arbitrary REST and YQL commands to the
Yahoo Fantasy Sports API, today that includes years 2001-2016.

Requirements
------------

* PHP 5+
* PHP Extensions: simplexml (default), PDO (default), PDO SQLite driver* (default), and OAuth (in PECL, NOT standard)
* Optionally edit to use a non-sqlite driver, as any PDO friendly DB should work (all queries are simple)
* Yahoo API Key: <https://developer.apps.yahoo.com/dashboard/createKey.html>
* setup.php checks for all of these requirements, so it'll yell at you if not ready
* Note: It's rare for a host to have or enable the OAuth extension. You must do this yourself.
* Todo: Remove OAuth extension requirement, and replace with a version that only requires Curl.

Installation
------------

* Place files in a web accessible directory
* Determine where the sqlite database will be loaded
* Modify inc/config.inc.php according to your setup
* Run setup.php
* It will either state problems or say installation was a success

Usage
------------

* Run authenticate.php to request and save a Yahoo Authentication token
* Run myinfo.php to view some information about yourself
* Run transactions.php to view transactions for a particular league of yours
* Run rest.php to execute arbitrary REST or YQL commands

Notes
------------

* Initially this project was named YahooFantasyMessages and only dealt with message posts, which is why leftover code exists
* This is not well tested and does contain bugs, although as of 2016 it works (for me)
* Please find and fix or report bugs here, and/or cleanup code
* The functionality is simple, and not pretty (e.g., ugly HTML) but it works
* Revoking OAuth tokens for Yahoo applications can be done here: <https://api.login.yahoo.com/WSLogin/V1/unlink>

Other
------------

This codebase is a hack, and not well thought out. There are other options out there, including:

* JavaScript: https://github.com/whatadewitt/yfsapi
    * Demo that's also useful for seeing what the Yahoo API is capable of: http://yfantasysandbox.herokuapp.com
    * Probably the most updated and modern Yahoo API codebase out there
* That's all for now, see google for others.

TODO
------------

* Important: the bulk of this code is rather old, so update it
* Important: allow non PECL OAuth extension users to utilize this, by optionally allowing the Yahoo oauth wrappers
* Find and report bugs, and fix them
* Add transaction archiving, and related graph generation
* Add prettier output instead of print_r() everywhere
* Test on other setups, and as other users
* Make it multi-user friendly, and authentication in general
* Allow non-sqlite PDO drivers to easily work
* Don't refresh the token so often (near every request)
* Make less rest calls by combining queries (and do more caching/local storage)
* Add data export options, like to CSV/Excel
* Add tools for local copies, like full-text search (or similar)
* Add documentation, explaining that both REST and YQL work
* Clean leftover code that's specific to the old YahooFantasyMessages project (done?)
* Change license to something simpler and more open, like MIT

License
------------

* All files here use the Apache 2.0 license: <http://www.apache.org/licenses/LICENSE-2.0.html>
* Author: Philip Olson <philip@roshambo.org>
