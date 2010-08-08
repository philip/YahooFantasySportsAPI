<?php
require './inc/config.inc.php';
require './inc/lib/yahoomessagesetup.php';
require './inc/header.php';

$errors = array();

// Check: required extensions
foreach ( array( 'oauth', 'simplexml', 'pdo', 'pdo_sqlite' ) as $ext ) {
	if ( !extension_loaded( $ext ) ) {
		$errors[] = "Extension not loaded: See <a href='http://php.net/$ext'>$ext</a> for details.";
	}
}

// @todo are these checks always correct? 100 and 40 string lengths? research this.
if (!defined('OAUTH_CONSUMER_KEY') || strlen(OAUTH_CONSUMER_KEY) !== 100) {
	$errors[] = 'OAUTH_CONSUMER_KEY must be properly set, which means 100 characters in length.';
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
		$errors[] = 'SQLite file already exists (this is already setup). See config.inc.php.';
	}
}

if ( !empty( $errors ) ) {
	echo 'ERROR: The setup could not take place due to the following reasons: ';
	echo '<ul>';
	foreach ( $errors as $error ) {
		echo '<li>', $error, '</li>', PHP_EOL;
	}
	echo '<li>Note: Your DB_PATH is set to: [', DB_PATH, '] in config.inc.php</li>';
	echo '</ul>';
	exit;
}

// Begin doing the setup
$s = new YahooMessageSetup();

// @todo document these
$s->createAuthTable();
$s->createMessageTable();

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
