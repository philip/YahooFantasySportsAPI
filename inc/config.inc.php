<?php
// Information from Yahoo YDN
define('OAUTH_CONSUMER_KEY',    '');
define('OAUTH_CONSUMER_SECRET', '');

// Does a few things to help debugging
// @todo make this more useful, it does nothing [very] useful currently
define('DEBUG_MODE', TRUE);

// Base URL (domain) for the application (when used via web)
define('APPLICATION_URL', 'http://example.com');

// BASEDIR for the application, so APPLICATION_URL . APPLICATION_BASEDIR === Full URL
define('APPLICATION_BASEDIR', '/optional_path');

// Full URL of the site
define('BASEURL', APPLICATION_URL . APPLICATION_BASEDIR);

// Path to the sqlite database we're going to use
// Web server needs permissions to create this, otherwise errors will result
define('DB_PATH', '/full/path/to/a/sqlite/db.sqlite');

// Where message_archive specific includes are stored. Feel free to hard code this value
define('INCLUDE_PATH', dirname(__FILE__) . '/');

// Optionally set timezone. We use time to know when tokens expire, so it's consistent too
// Setting in php.ini and removing this would be ideal
date_default_timezone_set('UTC');

// This is bad, but is helpful for lazy developers like myself. When this
// app is closer to bug free, stop doing this.
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Includes we use on every page
require INCLUDE_PATH . 'lib/functions.php';
require INCLUDE_PATH . 'lib/db.php';
require INCLUDE_PATH . 'lib/yahoofantasyapi.php';
