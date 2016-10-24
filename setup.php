<?php
require './inc/config.inc.php';
require './inc/lib/yahoofantasyapisetup.php';
require './inc/header.php';

$errors = array();

// Check: required extensions
foreach ( array( 'oauth', 'simplexml', 'pdo', 'pdo_sqlite' ) as $ext ) {
	if ( !extension_loaded( $ext ) ) {
		$errors[] = "Extension not loaded: See <a href='http://php.net/$ext'>$ext</a> for details.";
	}
}

// @todo are these checks always correct? 100 and 40 string lengths? research this.
$_kl = strlen(OAUTH_CONSUMER_KEY);
if (!defined('OAUTH_CONSUMER_KEY') || ($_kl !== 100 && $_kl !== 92)) {
	$errors[] = 'OAUTH_CONSUMER_KEY must be properly set, which means 100 or 92 characters in length. Your length is '. $_kl;
}
if (!defined('OAUTH_CONSUMER_SECRET') || strlen(OAUTH_CONSUMER_SECRET) !== 40) {
	$errors[] = 'OAUTH_CONSUMER_SECRET must be properly set, which means 40 characters in length.';
}

// @todo allow deleting/moving from here
if ( file_exists( DB_PATH ) ) {
	// A small file may have been created by accident, by PDO Open (which are 0 size, but 1024 is a nice number)
	if ( filesize( DB_PATH ) < 1024 ) {
		if ( !unlink( DB_PATH ) ) {
			$errors[] = 'SQLite file already exists, but is empty, yet I am unable to delete it. Please fix.';
		}
	} else {
		$errors[] = 'SQLite file already exists (this is already set up). See config.inc.php.';
	}
}

if ( !empty( $errors ) ) {
	echo "<h3>Not Ready to Set Up</h3>";
	echo '<p>ERROR: The setup could not take place due to the following reasons:</p>';
	echo '<ul>';
	foreach ( $errors as $error ) {
		echo '<li>', $error, '</li>', PHP_EOL;
	}
	echo '<li>Note: Your DB_PATH is set to: [', DB_PATH, '] in config.inc.php</li>';
	echo '</ul>';
	exit;
} else {
	echo "<h3>Begin Set Up</h3>";
	echo "<p>Everything seems ready, so let the set up begin.</p>";
}

// Begin doing the setup
$s = new YahooFantasyAPISetup();

// @todo document these
$s->createAuthTable();

// @todo use these
$s->createIdsTable();
$s->createTransactionsTable();

// @todo add a real check here
if ( file_exists( DB_PATH ) && filesize( DB_PATH ) > 1024 ) {
	echo '<br />Success. I think.', PHP_EOL;
	exit;
}

// @todo refer to other pages (e.g., request token) here

require './inc/footer.php';
