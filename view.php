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
	$rows = $m->getLocalMessages( $_GET['lk'] );

		foreach ($rows as $row) {
			echo '<p>Subject: ', $row['subject'], '</p>';
			echo '<p>', $row['text'], '</p>';
			echo '<p>By: ', $row['display_name'], ' (', $row['team_name'], ') on ', date('F d, Y', $row['timestamp']);
			echo '<hr />';
		}

	// @todo test this, make it possible to export
	//exportToCsv( (array) $rows, '/tmp/tmp.csv');
	echo '<h3>Remote Messages</h3>';
	echo '<p>Disabled, See code.</p>';
	/*
	echo '<pre>';
	$rows = $m->getMessages( 0, 100, $_GET['lk'] );
	print_r( $rows );
	echo '</pre>';
	*/
	
} catch( OAuthException $e ) {

	echo '<pre>';
	echo 'Exception caught!';
	echo 'Response: ', $e->lastResponse, PHP_EOL;
	print_r( $e );
}

require './inc/footer.php';

