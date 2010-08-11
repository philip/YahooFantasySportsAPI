<?php
/* 
	Wow, this hack grew beyond itself over time, but does indeed work. 
	@todo refector (which means, in the future, the API will change)
*/
class YahooMessageArchiver extends db {
	
	public $yurl = 'http://fantasysports.yahooapis.com/fantasy/v2/';
	public $log  = array();
	public $oauth;
	public $league_id;
	public $league_key;
	public $xoauth_yahoo_guid;
	
	public function __construct( $refresh = FALSE ) {

		// New oauth instance
		$this->oauth = new OAuth( OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION );

		// @todo Fix this, so we don't refresh token with every request. Check time_saved, as that's why it exists.
		if ( $refresh ) {
			$this->refreshAccess();
		}
	}

	public function fetch($query) {
		// Allow custom api urls, I guess. Not sure why :)
		if ( 0 !== strpos( $query, 'http://' ) ) {
			$query = $this->yurl . $query;
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

	// @todo why did I make getLocalMessages and getMessages have different signatures? Fix this madness.
	public function getMessages( $start = 0, $count = 100, $league_key = '' ) {
		if ( empty( $league_key ) ) {
			$league_key = $this->league_key;
		}
		
		$query = 'league/' . $league_key . '/messages;start=' . $start . ';count=' . $count;
		return $this->retrieve( $query );
	}

	public function getMessageCount($league_key) {
		$query = 'league/' . $league_key . '/messages;start=1;count=1';
		$info  = $this->retrieve( $query );
		return (int) $info->league->messages->message->message_id;
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
	
	public function insertMessage( $league_key, $data ) {
		if( !self::$dbh ) {
			$this->connect();
		}
		
		$league_id = substr( $league_key, strrpos( $league_key, '.' ) + 1 );
		
		// Consider not using "replace into"
		// @todo allow inserting only "new" messages
		$sql = "REPLACE INTO messages 
				(mid, message_id, subject, team_key, display_name, guid, team_name, timestamp, text, league_key, league_id)
				VALUES (:mid, :message_id, :subject, :team_key, :display_name, :guid, :team_name, :timestamp, :text, :league_key, :league_id)
		";
		try {
			$stmt = self::$dbh->prepare( $sql );
			
			$params = array(
				':mid'			=> (string) $data->message_id . '_' . (int) $league_id, // wanted something unique
				':message_id'	=> (string) $data->message_id,
				':subject'		=> (string) $data->subject,
				':team_key'		=> (string) $data->team_key,
				':display_name'	=> (string) $data->display_name,
				':guid'			=> (string) $data->guid,
				':team_name'	=> (string) $data->team_name,
				':timestamp'	=> (int)    $data->timestamp,
				':text'			=> (string) $data->text,
				':league_key'	=> (string) $league_key,
				':league_id'	=> (int)    $league_id,
			);
			$stmt->execute( $params );
			return TRUE;
		
		} catch ( PDOException $e ) {
			$this->log( 'could not run query: ' . $e->getMessage(), 'err', debug_backtrace() );
			$this->fatal_error( $e->getMessage() );
		}
	}
	
	public function insertMessages( $league_key, $datas ) {
		foreach ( $datas as $data ) {
			$this->insertMessage( $league_key, $data );
		}
	}
	
	public function getLocalMessages( $league_key ) {
		if( !self::$dbh ) {
			$this->connect();
		}
		
		$sql = "SELECT * FROM messages WHERE league_key = '$league_key'";
		try {
			$res  = self::$dbh->query( $sql );
			$rows = $res->fetchAll( PDO::FETCH_ASSOC );
			
			if ( empty( $row ) || count( $row ) === 0 ) {
				$this->log( 'no messages are stored', 'info', debug_backtrace());
			}
			return $rows;

		} catch (PDOException $e) {
			$this->log( 'could not run query: ' . $e->getMessage(), 'err', debug_backtrace() );
			$this->fatal_error( $e->getMessage() );
		}
	}

	// @todo update this for future compatibility, as game_keys are added over time
	// ^-- See also get_league_ids() in lib/functions.php
	public function getLeagueIds( $current = TRUE ) {
		
		if ( $current ) {
			$query = 'users;use_login=1/games;game_keys=nfl,nhl,nba,mlb/leagues';
		} else {
			$query = 'users;use_login=1/games;game_keys=57,49,79,101,124,153,175,199,222,242,58,62,78,102,125,154,176,200,223/leagues';
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
