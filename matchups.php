<?php
require './inc/config.inc.php';
require './inc/header.php';

// @todo add proper error reporting
if ( empty( $_VARS['week_num'] ) || empty( $_VARS['league_key'] ) ) {
	echo 'Error: A week number and league key is required for matchup information';
	exit;
}

try {
	$m = new YahooFantasyAPI( TRUE );
} catch ( OauthException $e ) {
	echo 'ERROR: Response: ', $e->lastResponse, PHP_EOL;
	exit;
}

$query = "league/{$_VARS['league_key']}/scoreboard;week={$_VARS['week_num']}/matchups/teams/roster;week={$_VARS['week_num']}/players/stats;type=week;week={$_VARS['week_num']}";

// For single team
// $query = "team/{$team_key}/roster;week={$week_num}/players/stats;type=week;week={$week_num}

$minfo = $m->retrieve( $query );

// @todo fix questionable error handling
if ( empty( $minfo ) ) {
	echo "FAIL";
	exit;
}

echo '<h1>Matchup information for week ', $minfo->league->scoreboard->week, '</h1>';

// @todo remove this hack
// @todo show more info
foreach ($minfo->league->scoreboard->matchups->matchup as $key => $values) {

	$team_name_0 = $values->teams->team[0]->name;
	$team_name_1 = $values->teams->team[1]->name;

	// @todo Assuming one manager for now
	$team_nick_0 = $values->teams->team[0]->managers->manager->nickname;
	$team_nick_1 = $values->teams->team[1]->managers->manager->nickname;

	$team_score_0 = $values->teams->team[0]->team_points->total;
	$team_score_1 = $values->teams->team[1]->team_points->total;

	$team_proj_0 = $values->teams->team[0]->team_projected_points->total;
	$team_proj_1 = $values->teams->team[1]->team_projected_points->total;

	echo "<h3>$team_name_0 ($team_nick_0) versus $team_name_1 ($team_nick_1)</h3>";
	echo "<p>$team_name_0 is projected for $team_proj_0 and so far has $team_score_0 points</p>";
	echo "<p>$team_name_1 is projected for $team_proj_1 and so far has $team_score_1 points</p>";

}


// @todo add proper view instead of simple print_r
print '<pre>';
print_r( $minfo );
print '</pre>';
require './inc/footer.php';
