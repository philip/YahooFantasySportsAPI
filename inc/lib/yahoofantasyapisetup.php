<?php
// @todo clean this up, a method per table needed?
class YahooFantasyApiSetup extends db {

	public $log;

	public function createAuthTable() {
		if( !self::$dbh ) {
			$this->connect();
		}
		$sql = "CREATE TABLE yahoo_auth (
			xoauth_yahoo_guid TEXT,
			oauth_token TEXT,
			oauth_verifier TEXT,
			oauth_token_secret TEXT UNIQUE,
			oauth_expires_in INTEGER,
			oauth_session_handle TEXT UNIQUE,
			oauth_authorization_expires_in TEXT,
			xoauth_request_auth_url TEXT,
			oauth_callback_confirmed INTEGER,
			time_saved INTEGER
		);";

		try {
			$res = self::$dbh->query( $sql );
			return TRUE;
		} catch ( PDOException $e ) {
			$this->fatal_error( $e->getMessage() );
		}
	}

	// Will this be used?
	public function createIdsTable() {
		if( !self::$dbh ) {
			$this->connect();
		}
		$sql = "CREATE TABLE ids (
			league_key TEXT PRIMARY KEY,
			league_id  INTEGER,
			xoauth_yahoo_guid TEXT
		);";
		try {
			$res = self::$dbh->query( $sql );
			return TRUE;
		} catch ( PDOException $e ) {
			$this->fatal_error( $e->getMessage() );
		}
	}

	public function createMessageTable() {
		if( !self::$dbh ) {
			$this->connect();
		}
		$sql = "CREATE TABLE messages (
			mid TEXT PRIMARY KEY,
			message_id INTEGER,
			subject TEXT,
			team_key TEXT,
			display_name TEXT,
			guid TEXT,
			team_name TEXT,
			timestamp INTEGER,
			text TEXT,
			league_key TEXT,
			league_id INTEGER
		);";
		try {
			$res = self::$dbh->query( $sql );
			return TRUE;
		} catch ( PDOException $e ) {
			$this->fatal_error( $e->getMessage() );
		}
	}

	// Not everything is here
	// Also, players->player has multiple players per transaction....
	public function createTransactionsTable() {
		if( !self::$dbh ) {
			$this->connect();
		}
		$sql = "CREATE TABLE transactions (
			transaction_key TEXT PRIMARY KEY,
			transaction_id INTEGER,
			type_generic TEXT,
			timestamp INTEGER,
			status TEXT,
			player_key TEXT,
			player_id INT,
			ascii_first TEXT,
			ascii_last TEXT,
			type TEXT,
			source_type TEXT,
			destination_type TEXT,
			destination_team_key TEXT,
			league_key TEXT,
			league_id INTEGER
		);";
		try {
			$res = self::$dbh->query( $sql );
			return TRUE;
		} catch ( PDOException $e ) {
			$this->fatal_error( $e->getMessage() );
		}
	}
}
