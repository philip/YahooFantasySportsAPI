<?php
require './inc/config.inc.php';
require './inc/header.php';

define('PHP_SELF', htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));

try {
	$m = new YahooMessageArchiver( TRUE );

} catch ( OauthException $e ) {
	echo 'ERROR: Response: ', $e->lastResponse, PHP_EOL;
	exit;
}

$info = $m->getStoredInfo();
echo '<h3>Stored authentication information</h3>', PHP_EOL;
echo '<p>GUID of user: ', $info['xoauth_yahoo_guid'], '</p>', PHP_EOL;
$guid = $info['xoauth_yahoo_guid'];

#$guid = $m->retrieve( 'http://social.yahooapis.com/v1/me/guid');
#if (!$guid->value) {
#	$guid = 'unknown';
#} else {
#	$guid = (string) $guid->value;
#}
?>

<h1>Run Yahoo arbritrary REST commands</h1>
<p>Note: The "http://fantasysports.yahooapis.com/fantasy/v2/" part is optional and the default.</p>
<p>Note: We determined your GUID is: <strong><?php echo $guid ?></strong> so replace {guid} with it where needed.</p>
<p>Note: This OAuth token is specific to Fantasy Sports although a lot of other (but not all) information can be accessed here too. Like your profile.</p>
<p>Examples:</p>
<ul>
 <li>View your team(s) info: users;use_login=1/games;game_keys=nfl,mlb,nba,nhl/teams [<a href="<?php echo PHP_SELF ?>?command=users;use_login=1/games;game_keys=nfl,mlb,nba,nhl/teams">run</a>]</li>
 <li>Get your GUID: http://social.yahooapis.com/v1/me/guid [<a href="<?php echo PHP_SELF ?>?command=http://social.yahooapis.com/v1/me/guid">run</a>]</li>
 <li>View your Yahoo profile: http://social.yahooapis.com/v1/user/{guid}/profile [<a href="<?php echo PHP_SELF ?>?command=http://social.yahooapis.com/v1/user/<?php echo $guid ?>/profile">run</a>]</li>
</ul>

<form action="<?php echo PHP_SELF ?>" method="GET">
<textarea name="command" cols="80" rows="10"><?php echo isset($_GET['command']) ? $_GET['command'] : ''; ?></textarea>
<input type="submit" name="submit" value="submit REST command">
<input type="hidden" name="action" value="run">
</form>

<?php

if ( isset( $_GET['command'] ) ) {

	try {

		$out = $m->retrieve( trim($_GET['command']) );
		print "<pre>";
		print_r($out);

	} catch( OAuthException $e ) {

		echo '<pre>';
		echo 'Exception caught!', PHP_EOL;
		echo 'Response: ', $e->lastResponse, PHP_EOL;
		print_r( $e );
	}

}

require './inc/footer.php';
