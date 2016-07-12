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

if ( isset($_GET['a']) && $_GET['a'] === '1' && isset( $_GET['lk'] ) ) {
	
	echo 'Hello, I am inserting messages now. ', PHP_EOL;

	$count= 0;
	$data = $m->getMessages( 1, 200, $_GET['lk'] );
	foreach ( $data->league->messages->message as $message ) {
		if ( $m->insertMessage( $_GET['lk'], $message ) ) {
			$count++;
		} else {
			// @todo :)
			echo 'FAIL', PHP_EOL;
		}
	}
	echo 'I inserted ', $count, ' messages', PHP_EOL;
}

$linfos = $m->getLeagueInfo( $ids );

// @todo reduce number of rest calls
echo '<dl>';
foreach ( $linfos->league as $linfo ) {

	$mcount = $m->getMessageCount(  $linfo->league_key );
	$lmess  = $m->getLocalMessages( $linfo->league_key );
	$lcount = count( $lmess );
	
	echo '<dt><a href="', $linfo->url, '">', $linfo->name, '</a></dt>', PHP_EOL;
	echo '<dd>Message counts: Remote: (', $mcount, ') and Local: (', $lcount, ')</dd>', PHP_EOL;
	echo '<dd>Archive remote messages to local: [<a href="messages.php?a=1&lk=', $linfo->league_key, '">execute</a>]</dd>', PHP_EOL;
	echo '<dd>View messages: [<a href="view_messages.php?lk=', $linfo->league_key, '">execute</a>]</dd>';
}
echo "</dl>";
