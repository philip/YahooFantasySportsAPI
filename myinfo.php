<?php
require './inc/config.inc.php';
require './inc/header.php';

try {
	$m = new YahooMessageArchiver(TRUE);
} catch (OauthException $e) {
	echo "ERROR: Response: " . $e->lastResponse . PHP_EOL;
	exit;
}

// @todo make output prettier instead of print_r() everywhere
try {
	print "<pre>";

	// Stored info
	echo "Stored info\n";
	$info = $m->getStoredInfo();
	print_r($info);

	// League Ids
	echo "League Ids from site\n";
	$ids = $m->getLeagueIds(TRUE);
	print_r($ids);
	
	echo "League Ids stored locally\n";
	$ids = $m->getLocalLeagueIds($m->xoauth_yahoo_guid);
	print_r($ids);
	
	echo "</pre>";

	// Past Games
	echo "Games you play in.\n";
	$data = $m->retrieve("users;use_login=1/games");
	$i = 1;
	echo "<table>";
	foreach ($data->users->user->games->game as $game) {
		$bgcolor = ($i & 1) ? '#ffffff' : '#eeeeee';
		$game = (array) $game;
		echo "<tr bgcolor='$bgcolor'>";
		echo "<td>", $game['season'], '</td>';
		echo "<td>", $game['code'], '</td>';
		echo '</tr>';
	}
	echo "</table>";
	

} catch(OAuthException $e) {

	print "<pre>";
	echo "Exception caught!\n";
	echo "Response: ". $e->lastResponse . "\n";
	print_r($e);
}

require './inc/footer.php';
