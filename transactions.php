<?php
// Types: add, drop, commish, trade
// @todo Do something useful here with transactions although they are archived at Yahoo alrady (no 100 limit)
require './inc/config.inc.php';
require './inc/header.php';

try {
	$m = new YahooFantasyAPI( TRUE );
} catch (OauthException $e) {
	echo 'ERROR: Response: ', $e->lastResponse, PHP_EOL;
	exit;
}

// @todo verify lid format (x.x.x)
// @todo verify proper type (add, drop, commish, trade, ... others?)
$lid  = empty( $_GET['lid'] )  ? FALSE : trim( $_GET['lid'] );
$type = empty( $_GET['type'] ) ? 'add' : trim( $_GET['type'] );

if (empty($lid)) {
	$lids = $m->getLeagueIds( FALSE );
	if ( !$lids ) {
		echo 'Error: Unable to find league ids for you.', PHP_EOL;
		exit;
	}
	echo "<h3>Choose a League ID</h3>";
	echo "<ul>";
	foreach ($lids as $lid) {
		echo "<li>";
		echo '<a href="transactions.php?lid='. $lid['league_key'] . '">'. $lid['name'] . ' ('. $lid['season'] . ')</a>';
		echo "</li>";
	}
	exit;
}

$transactions = $m->getTransactions($lid);

echo "<h3>Transactions for league id '$lid'</h3>";
echo '<table border="1">';
echo '<tr><th>Player</th><th>Type</th><th>Manager</th><th>Date</th></tr>';
$i = 0;
foreach ($transactions as $transaction) {

	$bgcolor = (++$i & 1) ? '#cccccc' : '#ffffff';

	echo "<tr bgcolor='$bgcolor'>";
	echo '<td>', $transaction['player_name'], ' (', $transaction['team'], ')</td>';
	echo '<td>', $transaction['vtype'], '</td>';
	echo '<td>', $m->teamnames[$transaction['manager']], '</td>';
	echo '<td>', date('Y m d', $transaction['timestamp']), '</td>';
	echo '</tr>';

}
echo '</table>';

require './inc/footer.php';
