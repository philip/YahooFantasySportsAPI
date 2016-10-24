<?php
/*
	Wow, this hack grew beyond itself over time, but does indeed work.
	@todo refector (which means, in the future, the API will change)
*/
class YahooFantasyAPI extends db {

	public $yurl = 'http://fantasysports.yahooapis.com/fantasy/v2/';
	public $yqlurl = "http://query.yahooapis.com/v1/yql?q=";
	public $log  = array();
	public $oauth;
	public $league_id;
	public $league_key;
	public $xoauth_yahoo_guid;
	public $teamnames = array();

	public function __construct( $refresh = FALSE ) {

		// New oauth instance
		$this->oauth = new OAuth( OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION );

		// @todo Fix this, so we don't refresh token with every request. Check time_saved, as that's why it exists.
		if ( $refresh ) {
			$this->refreshAccess();
		}
	}

	public function fetch($query) {
		$query = trim($query);
		// Allow custom api urls, I guess. Not sure why :)
		if ( 0 !== strpos( $query, 'http://' ) ) {
			if (0 === stripos( $query, 'select' ) ) {
				// Also alow YQL, assuming begins with a select
				$query = $this->yqlurl . urlencode($query);
			} else {
				$query = $this->yurl . $query;
			}
		}
		return $this->oauth->fetch( $query );
	}

	public function retrieve( $query ) {
		$out = $this->fetch( $query );
		if ( !$out ) {
			return FALSE;
		}
		if (!$response = $this->oauth->getLastResponse()) {
			$this->log( 'could not retrieve last oauth response', 'err', debug_backtrace() );
			return FALSE;
		}

		$data = simplexml_load_string($response);
		if ( !$data ) {
			$this->log( 'could not parse retrieved xml', 'err', debug_backtrace() );
			return FALSE;
		}
		return $data;
	}

	public function log( $message, $type, $backtrace = '' ) {
		$this->log[$type][] = array( 'message' => $message, 'backtrace' => $backtrace );
	}

	public function getLog() {
		return $this->log;
	}

	public function refreshAccess() {
		$access_local = $this->getStoredInfo();
		if ( empty( $access_local['xoauth_yahoo_guid'] ) ) {
			$this->log( 'No xoauth_yahoo_guid passed', 'err', debug_backtrace() );
			return false;
		}

		if( !self::$dbh ) {
			$this->connect();
		}

		// SQLite lacks "INSERT ... ON DUPLICATE KEY", so, I did this. Better ideas?
		$sql = "SELECT COUNT(*) FROM yahoo_auth WHERE xoauth_yahoo_guid = '$access_local[xoauth_yahoo_guid]'";
		try {
			$res  = self::$dbh->query( $sql );

			if ( $res->fetchColumn() > 0 ) {
				$sql = "
				UPDATE yahoo_auth SET xoauth_yahoo_guid=:xoauth_yahoo_guid, oauth_token=:oauth_token, oauth_token_secret=:oauth_token_secret, oauth_expires_in=:oauth_expires_in,
				oauth_session_handle=:oauth_session_handle, oauth_authorization_expires_in=:oauth_authorization_expires_in, time_saved=:time_saved
				WHERE xoauth_yahoo_guid = '$access_local[xoauth_yahoo_guid]'
				";
			} else {
				$sql = "
				INSERT INTO yahoo_auth (xoauth_yahoo_guid, oauth_token, oauth_token_secret, oauth_expires_in, oauth_session_handle, oauth_authorization_expires_in, time_saved)
				VALUES (:xoauth_yahoo_guid,:oauth_token,:oauth_token_secret,:oauth_expires_in,:oauth_session_handle,:oauth_authorization_expires_in, :time_saved)
				";
			}

		} catch ( PDOException $e ) {
			$this->log( 'could not run column count query: ' . $e->getMessage(), 'err', debug_backtrace() );
			$this->fatal_error( $e->getMessage() );
		}

		try {
			$this->oauth->setAuthType( OAUTH_AUTH_TYPE_URI );
			$this->oauth->setToken($access_local['oauth_token'], $access_local['oauth_token_secret'] );

			$access = $this->oauth->getAccessToken( 'https://api.login.yahoo.com/oauth/v2/get_token', $access_local['oauth_session_handle'] );
			$this->oauth->setAuthType( OAUTH_AUTH_TYPE_AUTHORIZATION );

			$this->oauth->setToken($access['oauth_token'], $access['oauth_token_secret'] );

			$stmt = self::$dbh->prepare( $sql );
			$params = array(
				':xoauth_yahoo_guid'				=> $access['xoauth_yahoo_guid'],
				':oauth_token'						=> $access['oauth_token'],
				':oauth_token_secret'				=> $access['oauth_token_secret'],
				':oauth_expires_in'					=> $access['oauth_expires_in'],
				':oauth_session_handle'				=> $access['oauth_session_handle'],
				':oauth_authorization_expires_in'	=> $access['oauth_authorization_expires_in'],
				':time_saved'						=> time() + (int) $access['oauth_expires_in'],
			);
			$stmt->execute( $params );

		} catch ( PDOException $e ) {
			$this->log( 'could not run query: ' . $e->getMessage(), 'err', debug_backtrace() );
			$this->fatal_error( $e->getMessage() );
		}

		$this->xoauth_yahoo_guid = $access['xoauth_yahoo_guid'];
		return TRUE;
	}

	public function saveAccess( $access, $oauth_verifier ) {

		if ( empty( $access['xoauth_yahoo_guid'] ) ) {
			$this->log( 'no xoauth_yahoo_guid passed', 'err', debug_backtrace() );
			return FALSE;
		}

		if( !self::$dbh ) {
			$this->connect();
		}

		// SQLite lacks "INSERT ... ON DUPLICATE KEY", so, I did this. Better ideas?
		$sql = "SELECT COUNT(*) FROM yahoo_auth WHERE xoauth_yahoo_guid = '$access[xoauth_yahoo_guid]'";
		try {
			$res = self::$dbh->query( $sql );

			if ( $res->fetchColumn() > 0 ) {
				// Update
				$sql = "
				UPDATE yahoo_auth SET xoauth_yahoo_guid=:xoauth_yahoo_guid, oauth_token=:oauth_token, oauth_verifier=:oauth_verifier, oauth_token_secret=:oauth_token_secret,
				oauth_expires_in=:oauth_expires_in, oauth_session_handle=:oauth_session_handle, oauth_authorization_expires_in=:oauth_authorization_expires_in, time_saved=:time_saved
				WHERE xoauth_yahoo_guid = '$access[xoauth_yahoo_guid]'
				";
			} else {
				// Insert
				$sql = "
				INSERT INTO yahoo_auth (xoauth_yahoo_guid, oauth_token, oauth_verifier, oauth_token_secret, oauth_expires_in, oauth_session_handle, oauth_authorization_expires_in, time_saved)
				VALUES (:xoauth_yahoo_guid,:oauth_token,:oauth_verifier,:oauth_token_secret,:oauth_expires_in,:oauth_session_handle,:oauth_authorization_expires_in,:time_saved)
				";
			}

		} catch ( PDOException $e ) {
			$this->log( 'could not run column count query: ' . $e->getMessage(), 'err', debug_backtrace() );
			$this->fatal_error( $e->getMessage() );
		}

		try {
			$stmt = self::$dbh->prepare( $sql );
			$params = array(
				':xoauth_yahoo_guid'				=> $access['xoauth_yahoo_guid'],
				':oauth_token'						=> $access['oauth_token'],
				':oauth_verifier'					=> $oauth_verifier,
				':oauth_token_secret'				=> $access['oauth_token_secret'],
				':oauth_expires_in'					=> $access['oauth_expires_in'],
				':oauth_session_handle'				=> $access['oauth_session_handle'],
				':oauth_authorization_expires_in'	=> $access['oauth_authorization_expires_in'],
				':time_saved'						=> time() + (int) $access['oauth_expires_in'],
			);

			$stmt->execute( $params );

		} catch ( PDOException $e ) {
			$this->log( 'could not run query: ' . $e->getMessage(), 'err', debug_backtrace() );
			$this->fatal_error( $e->getMessage() );
		}
		return TRUE;
	}

	public function saveRequest( $request ) {

		if ( empty( $request['oauth_token_secret'] ) ) {
			$this->log( 'No oauth_token_secret passed', 'err', debug_backtrace() );
			return FALSE;
		}
		if( !self::$dbh ) {
			$this->connect();
		}

		// SQLite lacks "INSERT ... ON DUPLICATE KEY", so, I did this. Better ideas?
		$sql = "SELECT COUNT(*) FROM yahoo_auth WHERE oauth_token_secret = '$request[oauth_token_secret]'";
		try {
			$res  = self::$dbh->query( $sql );

			if ( $res->fetchColumn() > 0 ) {
				$sql =
				"
					UPDATE yahoo_auth SET oauth_token=:oauth_token, oauth_token_secret=:oauth_token_secret, oauth_expires_in=:oauth_expires_in,
					xoauth_request_auth_url=:xoauth_request_auth_url, oauth_callback_confirmed=:oauth_callback_confirmed, time_saved=:time_saved
					WHERE oauth_token_secret = '$request[oauth_token_secret]'
				";
			} else {
				$sql =
				"
					INSERT INTO yahoo_auth (oauth_token, oauth_token_secret, oauth_expires_in, xoauth_request_auth_url, oauth_callback_confirmed, time_saved)
					VALUES (:oauth_token, :oauth_token_secret, :oauth_expires_in, :xoauth_request_auth_url, :oauth_callback_confirmed, :time_saved)
				";
			}

		} catch ( PDOException $e ) {
			$this->log( 'could not run column count query: ' . $e->getMessage(), 'err', debug_backtrace() );
			$this->fatal_error( $e->getMessage() );
		}

		try {
			$stmt = self::$dbh->prepare( $sql );
			$params = array(
				':oauth_token'						=> $request['oauth_token'],
				':oauth_token_secret'				=> $request['oauth_token_secret'],
				':oauth_expires_in'					=> $request['oauth_expires_in'],
				':xoauth_request_auth_url'			=> $request['xoauth_request_auth_url'],
				':oauth_callback_confirmed'			=> $request['oauth_callback_confirmed'],
				':time_saved'						=> time() + (int) $request['oauth_expires_in'],
			);

			$stmt->execute( $params );

		} catch ( PDOException $e ) {
			$this->log( 'could not run query: ' . $e->getMessage(), 'err', debug_backtrace() );
			$this->fatal_error( $e->getMessage() );
		}
		return TRUE;
	}

	public function getStoredInfo() {
		if( !self::$dbh ) {
			$this->connect();
		}
		try {
			// @todo do we really need to keep them all? Fix this hack
			$res  = self::$dbh->query( 'SELECT * FROM yahoo_auth ORDER BY time_saved DESC LIMIT 1' );
			$row  = $res->fetch( PDO::FETCH_ASSOC );

			if ( empty($row) || count($row) === 0 ) {
				$this->log( 'info is not stored', 'info', debug_backtrace() );
			}
			return $row;

		} catch ( PDOException $e ) {
			$this->log( 'could not run query: ' . $e->getMessage(), 'err', debug_backtrace() );
			$this->fatal_error( $e->getMessage() );
		}
	}

	public function getLeagueInfo( $ids ) {
		if ( is_array( $ids ) && count( $ids ) < 1 ) {
			return false;
		}
		if ( is_string( $ids ) ) {
			$ids = array( $ids );
		}

		$lkeys = '';
		foreach ( $ids as $id ) {
			$lkeys .= $id['league_key'] . ',';
		}
		$lkeys = rtrim( $lkeys, ',' );

		$linfo = $this->retrieve( "leagues;league_keys=$lkeys" );

		return $linfo->leagues;
	}

	// @todo update this for future compatibility, as game_keys are added over time
	// ^-- See also get_league_ids() in lib/functions.php
	// Today it contains 2001 - 2016
	public function getLeagueIds( $current = TRUE ) {

		if ( $current ) {
			$query = 'users;use_login=1/games;game_keys=nfl,nhl,nba,mlb/leagues';
		} else {
			$query = 'users;use_login=1/games;game_keys=57,49,79,101,124,153,175,199,222,242,58,62,78,102,125,154,176,200,223,242,257,273,314,331,348,359/leagues';
		}

		$data = $this->retrieve( $query );
		if ( !$data || count( $data ) === 0 ) {
			return false;
		}
		foreach ( $data->users->user->games->game as $game ) {
			foreach ( $game->leagues->league as $league ) {
				$info[] = (array) $league;
			}
		}
		return $info;
	}

	// TODO: Not sure if storing league ids works at the moment
	public function getLocalLeagueIds( $xoauth_yahoo_guid ) {
		if( !self::$dbh ) {
			$this->connect();
		}

		$sql = "SELECT * FROM ids WHERE xoauth_yahoo_guid = '$xoauth_yahoo_guid'";
		try {
			$res  = self::$dbh->query( $sql );
			$rows = $res->fetchAll( PDO::FETCH_ASSOC );

			if ( empty( $row ) || count( $row ) === 0 ) {
				$this->log('no ids for $xoauth_yahoo_guid are stored', 'info', debug_backtrace() );
			}
			return $rows;

		} catch ( PDOException $e ) {
			$this->log( 'could not run query: ' . $e->getMessage(), 'err', debug_backtrace() );
			$this->fatal_error( $e->getMessage());
		}
	}

	// @todo add all information, like url, name, etc. as per getLeagueIds().
	public function storeLeagueIds( $xoauth_yahoo_guid ) {
		$ids = $this->getLeagueIds( TRUE );
		if ( empty( $xoauth_yahoo_guid ) || empty( $ids ) ) {
			return FALSE;
		}
		if( !self::$dbh ) {
			$this->connect();
		}

		foreach ( $ids as $id ) {
			$sql = "REPLACE INTO ids
					(xoauth_yahoo_guid, league_id, league_key)
					VALUES (:xoauth_yahoo_guid, :league_id, :league_key)
			";
			try {
				$stmt = self::$dbh->prepare( $sql );

				$params = array(
					':xoauth_yahoo_guid'	=> (string) $xoauth_yahoo_guid,
					':league_id'			=> (int)    $id['league_id'],
					':league_key'			=> (string) $id['league_key'],
				);
				$stmt->execute( $params );
				return TRUE;

			} catch ( PDOException $e ) {
				$this->log( 'could not run query: ' . $e->getMessage(), 'err', debug_backtrace() );
				$this->fatal_error( $e->getMessage() );
			}
		}
	}

	public function getTransactions($league_key) {
		$rest = $this->yurl . "leagues;league_keys=". $league_key ."/transactions";
		$out = $this->retrieve($rest);
		if (!$out) {
			$this->log( 'transaction retrieval failed when executing: '. $rest, 'err', debug_backtrace() );
			return false;
		}
		$a = $this->xml2array($out->leagues->league->transactions);

		#echo "<pre>";
		#print_r($a);
		#exit;

		$trans = array();
		foreach ($a['transaction'] as $t) {

			if (!empty($t['players'])) {
				// Hack to get around API where add/drop = array but add or drop do not.
				if (!empty($t['players']['player']['player_key'])) {
					$_tmp = $t['players']['player'];
					unset($t['players']['player']);
					$t['players']['player'][0] = $_tmp;
				}

				$timestamp = is_numeric($t['timestamp']) ? (int) $t['timestamp'] : strtotime($t['timestamp']);

				// TODO: Most code assumes FAAB is used, those without FAAB might see $0 for all waiver moves.
				// TODO: If available, see if waiver position data is available when not using FAAB
				if (isset($t['faab_bid'])) {
					$bid = (int) $t['faab_bid'];
				} else {
					$bid = 0;
				}

				foreach ($t['players']['player'] as $player) {

					$team_key  = empty($player['transaction_data']['destination_team_key'])  ? $player['transaction_data']['source_team_key']  : $player['transaction_data']['destination_team_key'];
					$team_name = empty($player['transaction_data']['destination_team_name']) ? $player['transaction_data']['source_team_name'] : $player['transaction_data']['destination_team_name'];

					// While we're here, let's get team names
					// Name from most recent transaction will be assigned to the team key
					@$this->teamnames[$team_key] = $team_name;

					// One way to display / view this information
					$vtype = "";
					$transaction = $player['transaction_data'];
					if ($transaction['source_type'] === 'freeagents') {
						$vtype = "FA";
					} elseif ($transaction['source_type'] === 'waivers') {
						if (empty($transaction['bid'])) {
							$vtype = '$0';
						} else {
							$vtype = '$' . (int) $transaction['bid'];
						}
					} else {
						if ($transaction['type'] === 'trade') {
							$vtype = 'Trade';
						} elseif ($transaction['type'] === 'drop') {
							$vtype = 'Drop';
						} else {
							$vtype = 'Uknown';
						}
					}

					$trans[] = array(
						'player_key'	=> $player['player_key'],
						'player_name'	=> $player['name']['full'],
						'type'			=> $player['transaction_data']['type'],
						'vtype'			=> $vtype,
						'source_type'	=> $player['transaction_data']['source_type'],
						'team'			=> $player['editorial_team_abbr'],
						'timestamp'		=> $timestamp,
						'manager'		=> $team_key,
						'bid'			=> $bid,
					);

				}
			}
		}
		return $trans;
	}

	public function getTeamFromTransaction($transaction, $type = 'key') {

		$transaction = (array) $transaction;

		if ($type === 'key') {
			if (!empty($transaction['destination_team_key'])) {
				$key =  $transaction['destination_team_key'];
			} elseif (!empty($transaction['source_team_key'])) {
				$key = $transaction['source_team_key'];
			} else {
				$key = 'unknown';
			}
			return $key;
		}

		if ($type === 'name') {
			if (!empty($transaction['destination_team_name'])) {
				$name =  $transaction['destination_team_name'];
			} elseif (!empty($transaction['source_team_name'])) {
				$name = $transaction['source_team_name'];
			} else {
				$name = 'unknown';
			}
			return $name;
		}
	}

	// @todo why did I make getLocalMessages and getMessages have different signatures? Fix this madness.
	// Note: message board posts are no longer mentioned in the official API, so it must be deprecated
	public function getMessages( $start = 0, $count = 100, $league_key = '' ) {
	    if ( empty( $league_key ) ) {
	        $league_key = $this->league_key;
	    }
	    $query = 'league/' . $league_key . '/messages;start=' . $start . ';count=' . $count;
	    return $this->retrieve( $query );
	}

	// Note: message board posts are no longer mentioned in the official API, so it must be deprecated
	public function getMessageCount($league_key) {
	    $query = 'league/' . $league_key . '/messages;start=1;count=1';
	    $info  = $this->retrieve( $query );
		if (is_numeric( (string) $info->league->messages->message->message_id)) {
			return (int) $info->league->messages->message->message_id;
		} else {
			return false;
		}
	}

	// TODO: this code was written when XML was available via the API, but consider rewrite using JSON only
	private function xml2array ( $xmlObject, $out = array () )
	{
	        foreach ( (array) $xmlObject as $index => $node ) {
	            $out[$index] = ( is_object ( $node ) ||  is_array ( $node ) ) ? $this->xml2array ( $node ) : $node;
			}
	        return $out;
	}

	// Don't remember if I use this, but it was temporary
	public static function printR( $var, $title = '', $exit = FALSE ) {
	    if ( $title ) {
	        echo '<h2>', $title, '</h2>', PHP_EOL;
	    }
	    echo '<pre>', PHP_EOL;
	    print_r( $var );
	    echo '</pre>', PHP_EOL;
	    if ( $exit ) {
	        exit;
	    }
	}
}
