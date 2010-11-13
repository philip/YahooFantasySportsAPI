<?php
require './inc/config.inc.php';
require './inc/header.php';
?>
<p>
 This simply links to pages that can be useful accessing via the web.
</p>
<ul>
 <li><a href="setup.php">setup</a></li>
 <li><a href="authenticate.php">authenticate to yahoo</a></li>
 <li><a href="myinfo.php">see your info</a></li>
 <li><a href="messages.php">create and view message archives</a></li>
 <li><a href="transactions.php">create and view transaction archives</a></li>
 <li><a href="rest.php">Run arbritrary rest commands</a></li>
</ul>
<p>
 Links of interest (that were used while developing this):
</p>
<ul>
 <li><a href="http://developer.yahoo.com/oauth/guide/oauth-auth-flow.html">Y! OAuth flow</a></li>
 <li><a href="http://php.net/oauth">PHP OAuth documentation</a></li>
 <li><a href="http://developer.yahoo.com/fantasysports/guide/">Y! Fantasy Sports Dev Guide</a></li>
 <li><a href="http://developer.yahoo.com/oauth/guide/oauth-errors.html">Y! OAuth error codes</a></li>
 <li><a href="http://developer.yahoo.net/forum/?showforum=122">Yahoo Sports Forum</a></li>
 <li><a href="http://developer.yahoo.net/forum/index.php?showforum=42">Yahoo OAuth Forum</a></li>
 <li><a href="https://api.login.yahoo.com/WSLogin/V1/unlink">Revoke Yahoo OAuth tokens here</a></li>
</ul>
<p>
This is an Open Source application written by Philip Olson, and hosted as <a href="http://github.com/philip/YahooFantasySportsAPI">YahooFantasySportsAPI</a> on GitHub.
</p>

<?php
require './inc/footer.php';
