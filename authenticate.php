<?php
/*	Authentication flow. Requests and stores an OAuth based token. See the following for details:
	- http://developer.yahoo.com/oauth/guide/oauth-auth-flow.html
*/
require './inc/config.inc.php';

try {

	$o = new OAuth( OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI );
	$o->enableDebug();

	$m = new YahooFantasyAPI( FALSE );

	/********************************************************************************************************************************************
	/** Stage #1: Request Token
	/********************************************************************************************************************************************/
	if ( empty( $_GET['oauth_token'] ) && empty( $_GET['oauth_verifier'] ) ) {

		$response = $o->getRequestToken( 'https://api.login.yahoo.com/oauth/v2/get_request_token', APPLICATION_URL . $_SERVER['SCRIPT_NAME'] );

		if ( $response && is_array( $response ) ) {

			if ( !$m->saveRequest( $response ) ) {
				echo "Unable to save response.";
				$m->printR( $m->getLog() );
				exit;
			}

			// @todo add error reporting
			if ( empty( $response['xoauth_request_auth_url'] ) ) {
				$m->printR( $m->getLog() );
				echo 'Error: Did not receive authentication url. Hmm...', PHP_EOL;
				exit;
			}

			header('Location: ' . $response['xoauth_request_auth_url']);
			exit;

		} else {
			// @todo add error reporting
			echo 'Error: Unknown problem.' . PHP_EOL;
			exit;
		}
	}

	/********************************************************************************************************************************************
	/** Stage #2: Access Token
	/********************************************************************************************************************************************/
	if ( !$request_token_info = $m->getStoredInfo() ) {
		echo 'Error: Unable to locate request token information. Try requesting it again.', PHP_EOL;
		$m->printR( $m->getLog() );
		exit;
	}

	if ( empty( $request_token_info ) ) {
		echo 'Error: no request information, info is empty. Try requesting it again.', PHP_EOL;
		exit;
	}

	if ( $request_token_info && is_array( $request_token_info ) ) {

		$o->setToken( $request_token_info['oauth_token'], $request_token_info['oauth_token_secret'] );

		if ( empty( $_GET['oauth_verifier'] ) ) {
			echo 'Missing oauth_verifier.', PHP_EOL;
			exit;
		}

		$response = $o->getAccessToken( 'https://api.login.yahoo.com/oauth/v2/get_token', NULL, $_GET['oauth_verifier'] );
		if ( !$response ) {
			echo 'Error: Received invalid access token. Try again.', PHP_EOL;
			exit;
		}

		$m->saveAccess( $response, $_GET['oauth_verifier'] );

		// Here means we likely have a nice access_token, so are ready to rock
		header('Location: ' . BASEURL);
		exit;
	}

} catch( OAuthException $e ) {
	echo '<pre>';
	echo 'Response: ', $e->lastResponse, PHP_EOL;
	print_r( $e );
	print_r( $o->debugInfo );
}
