<?php
require './inc/config.inc.php';
require './inc/header.php';

try {
	$m = new YahooFantasyAPI( TRUE );
} catch ( OauthException $e ) {
	echo "ERROR: Response: " . $e->lastResponse . PHP_EOL;
	exit;
}

try {

	$info = $m->getStoredInfo();
	$ids  = $m->getLeagueIds( FALSE );

	// Get league keys
	foreach ( $ids as $id ) {
		// @todo A few are missing this, research this
		if ( FALSE === strpos( $id['url'], 'http://' ) ) {
			$id['url'] = 'http://football.fantasysports.yahoo.com' . $id['url'];
		}

		$ldata[] = array(
			'league_key'	=> $id['league_key'],
			'league_id'		=> $id['league_id'],
			'url'			=> $id['url'],
			'season'		=> $id['season'],
			'name'			=> $id['name'],
		);
	}

	echo "<h3>All leagues you have played in</h3>";
	echo "<ul>";
	foreach ($ldata as $data) {
		echo "<li><a href='{$data['url']}'>{$data['name']} ({$data['season']})</a></li>";
	}
	echo "</ul>";

} catch( OAuthException $e ) {

	print "<pre>";
	echo "Exception caught!\n";
	echo "Response: ". $e->lastResponse . "\n";
	print_r( $e );
}

require './inc/footer.php';
