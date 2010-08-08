<?php
require './inc/config.inc.php';
require './inc/header.php';

if ( empty( $_GET['lk'] ) ) {
	echo 'A league key is required', PHP_EOL;
	exit;
}

try {
	$m = new YahooMessageArchiver( TRUE );
} catch ( OauthException $e ) {
	echo 'ERROR: Response: ', $e->lastResponse, PHP_EOL;
	exit;
}

// @todo Make output prettier and not print_r() everywhere
try {
	echo '<h3>Local Messages</h3>';
	echo '<pre>';
	$rows = $m->getLocalMessages( $_GET['lk'] );
	print_r( $rows );
	echo '</pre>';

	// @todo test this, make it possible to export
    //exportToCsv( (array) $rows, '/tmp/tmp.csv');
	
	echo '<h3>Remote Messages</h3>';
	echo '<pre>';
	$rows = $m->getMessages( 0, 100, $_GET['lk'] );
	print_r( $rows );
	echo '</pre>';

	
} catch( OAuthException $e ) {

	echo '<pre>';
	echo 'Exception caught!';
	echo 'Response: ', $e->lastResponse, PHP_EOL;
	print_r( $e );
}

require './inc/footer.php';
