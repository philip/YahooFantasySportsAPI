<?php
require './inc/config.inc.php';
require './inc/header.php';
?>
<h3>Welcome</h3>
<p>Before playing with this rough application, you must first authenticate with your Yahoo login.</p>
<ol>
 <li>First, check if your system is ready: <a href="setup.php">Ready for Set Up?</a></li>
 <li>Next, authenticate: <a href="authenticate.php">Authenticate using Yahoo</a></li>
 <li>Next, see if it worked: <a href="myinfo.php">See your Fantasy Sports related information</a></li>
 <li>You might want to <a href="transactions.php">view transaction archives</a> for a particular league</li>
 <li>Or you might <a href="rest.php">execute arbitrary REST or YQL commands</a></li>
</ol>
<h3>See Also</h3>
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
<h3>Notes</h3>
<p>Although the Yahoo Fantasy Sports API is functioning, it is no longer actively maintained
    by Yahoo. For this reason, you will notice the official documentation is broken, and that
    the Fantasy Sports forum no longer exists. However, the API functions so have fun!
</p> 

<?php
require './inc/footer.php';
