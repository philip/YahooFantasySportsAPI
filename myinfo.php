<?php
require './inc/config.inc.php';
require './inc/header.php';

try {
	$m = new YahooMessageArchiver( TRUE );
} catch ( OauthException $e ) {
	echo "ERROR: Response: " . $e->lastResponse . PHP_EOL;
	exit;
}

// @todo make output prettier instead of print_r() everywhere
try {
	
	$info = $m->getStoredInfo();
	echo '<h3>Stored authentication information</h3>', PHP_EOL;
	echo '<p>GUID of user: ', $info['xoauth_yahoo_guid'], '</p>', PHP_EOL;
	echo '<p>Additional information is saved.</p>';
	
	echo '<h3>All leagues you have played in</h3>', PHP_EOL;
	$ids = $m->getLeagueIds( FALSE );

	echo '<dl>';
	foreach ( $ids as $id ) {
		// @todo A few are missing this, research this
		if ( FALSE === strpos( $id['url'], 'http://' ) ) {
			$id['url'] = 'http://football.fantasysports.yahoo.com' . $id['url'];
		}
		echo '<dt><a href="', $id['url'], '">', $id['name'], '</a></dt>', PHP_EOL;
		echo '<dd>League Key: ', $id['league_key'], ' with ', $id['num_teams'], ' teams</dd>', PHP_EOL;
		
		// @todo Somtimes an empty object, research this, because league has not started? What does 'update' mean?
		$time = $id['league_update_timestamp'];
		if ( is_object( $time ) ) {
			$time = 'unknown';
		} else {
			$time = date( 'F d, Y', $id['league_update_timestamp'] );
		}
		echo '<dd>Date of last update: ', $time, '</dd>', PHP_EOL;
	}
	echo '</dl>';
	
	// @todo not yet implemented, really
	$ids = $m->getLocalLeagueIds( $m->xoauth_yahoo_guid );
	if ($ids) {
		echo '<h3>League Ids stored locally</h3>', PHP_EOL;
		echo '<pre>';
		print_r( $ids );
		echo '</pre>';
	}

} catch( OAuthException $e ) {

	print "<pre>";
	echo "Exception caught!\n";
	echo "Response: ". $e->lastResponse . "\n";
	print_r( $e );
}

require './inc/footer.php';
