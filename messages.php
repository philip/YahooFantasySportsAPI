<?php
require './inc/config.inc.php';
require './inc/header.php';

try {
	$m = new YahooFantasyAPI( TRUE );
} catch (OauthException $e) {
	echo 'ERROR: Response: ', $e->lastResponse, PHP_EOL;
	exit;
}

$ids = $m->getLeagueIds( FALSE );
if ( !$ids ) {
	echo 'Error: Unable to find league ids for you.', PHP_EOL;
	exit;
}

if ( isset($_GET['v']) && $_GET['v'] === '1' && isset( $_GET['lk'] ) ) {

	echo 'Hello, I am getting messages. ', PHP_EOL;

	$count= 0;
	$data = $m->getMessages( 1, 100, $_GET['lk'] );
	foreach ( $data->league->messages->message as $message ) {
		echo "<pre>";
		print_r($message);
		echo "</pre>";
	}
}

$linfos = $m->getLeagueInfo( $ids );

// @todo reduce number of rest calls
echo '<dl>';
foreach ( $linfos->league as $linfo ) {

	if (!$mcount = $m->getMessageCount(  $linfo->league_key )) {
		$mcount = "Count unavailable, no longer available to the Y! API";
	}

	echo '<dt><a href="', $linfo->url, '">', $linfo->name, '</a></dt>', PHP_EOL;
	echo '<dd>Message count = ', $mcount, '</dd>', PHP_EOL;
	echo '<dd>View messages: [<a href="messages.php?lk=', $linfo->league_key, '&v=1">view them</a>]</dd>';
}
echo "</dl>";
