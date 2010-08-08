<?php
require './inc/config.inc.php';
require './inc/header.php';

try {
	$m = new YahooMessageArchiver( TRUE );
} catch (OauthException $e) {
	echo 'ERROR: Response: ', $e->lastResponse, PHP_EOL;
	exit;
}

$ids = $m->getLeagueIds( TRUE );
if ( !$ids ) {
	echo 'Error: Unable to find league ids for you.', PHP_EOL;
	exit;
}

// Types: add, drop, commish, trade
// @todo Do something useful here with transactions although they are archived at Yahoo alrady (no 100 limit)
echo "I do nothing yet. One day, however, I will be useful.";

require './inc/footer.php';
