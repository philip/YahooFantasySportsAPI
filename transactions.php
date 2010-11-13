<?php
// Types: add, drop, commish, trade
// @todo Do something useful here with transactions although they are archived at Yahoo alrady (no 100 limit)
require './inc/config.inc.php';
require './inc/header.php';
	
try {
	$m = new YahooMessageArchiver( TRUE );
} catch (OauthException $e) {
	echo 'ERROR: Response: ', $e->lastResponse, PHP_EOL;
	exit;
}

// @todo verify lid format (x.x.x)
// @todo verify proper type (add, drop, commish, trade, ... others?)
$lid  = empty( $_GET['lid'] )  ? FALSE : trim( $_GET['lid'] );
$type = empty( $_GET['type'] ) ? 'add' : trim( $_GET['type'] );

/*
if (empty($lids)) {
	$lids = $m->getLeagueIds( TRUE );
	if ( !$lids ) {
		echo 'Error: Unable to find league ids for you.', PHP_EOL;
		exit;
	}
}
*/

if (empty( $lid )) {
	$lid = '238.l.627060'; // public baseball league, for testing
}
	
$transactions = $m->retrieve( "http://fantasysports.yahooapis.com/fantasy/v2/league/{$lid}/transactions;type=$type" );

echo "<p>Query is for league id '$lid' searching for transaction types '$type'</p>";

echo '<table border="1">';
foreach ($transactions->league->transactions->transaction as $t) {
		
	$team_key = 'unknown';
	if ( isset( $t->players->player->transaction_data->destination_team_key ) ) {
		$team_key = (string) $t->players->player->transaction_data->destination_team_key;
	}

	if ( empty( $team_names[$team_key] ) ) {
		$team_names[$team_key] = $team_key;
	}

	echo '<tr>';
	echo '<td>', $t->players->player->name->full, '</td>';
	echo '<td>', $team_names[$team_key] , '</td>';
	echo '<td>', date('Y m d', (integer) $t->timestamp), '</td>';
	echo '<td>', $t->players->player->transaction_data->source_type, '</td>';
	echo '<td>', $t->status, '</td>';
	echo '</tr>';

}
echo '</table>';

require './inc/footer.php';
