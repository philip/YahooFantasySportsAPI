<?php
require './inc/config.inc.php';
require './inc/header.php';

try {
	$m = new YahooMessageArchiver( TRUE );
} catch ( OauthException $e ) {
	echo 'ERROR: Response: ', $e->lastResponse, PHP_EOL;
	exit;
}
?>

<form type="GET" action="<?php echo htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
Week Number: <input type="text" name="week_num" value="<?php echo $_VARS['week_num']; ?>"><br />
League Key: <input type="text" name="league_key" value="<?php echo $_VARS['league_key']; ?>"><br />
Team Number: <input type="text" name="team_num" value="<?php echo $_VARS['team_num']; ?>"><br />
<input type="submit" name="submit" value="submit">
</form>

<?php

require './inc/footer.php';
