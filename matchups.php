<?php
require './inc/config.inc.php';
require './inc/header.php';

// @todo add proper validation
$week_num   = empty($_GET['week_num'])   ? FALSE : (int)    $_GET['week_num'];
$league_key = empty($_GET['league_key']) ? FALSE : (string) $_GET['league_key'];

// @todo add proper error reporting
if (empty($week_num) || empty($league_key)) {
	echo "Error: A week number and league key is required for matchup information";
	exit;
}

try {
	$m = new YahooMessageArchiver( TRUE );
} catch ( OauthException $e ) {
	echo 'ERROR: Response: ', $e->lastResponse, PHP_EOL;
	exit;
}

$query = "league/{$league_key}/scoreboard;week=$week_num/matchups/teams/roster;week=$week_num/players/stats;type=week;week=$week_num";

// For single team
// $query = "team/{$team_key}/roster;week={$week_num}/players/stats;type=week;week={$week_num}

$minfo = $m->retrieve( $query );

// @todo add proper view instead of simple print_r
print "<pre>";
print_r($minfo);

