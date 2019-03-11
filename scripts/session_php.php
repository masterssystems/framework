<?php
## Datenstruktur initialisieren #
  unset( $session );
  session_set_cookie_params( 0, '/', '.'.$request['domain'].'.'.$request['tld'], false, false );
  session_start();

	# handle logout #
	if( $get['logout'] ) {
		session_unset();
		sql( 'DELETE FROM tbsession WHERE sid = "'.$session['sid'].'";' );
		$get['page'] = FRAMEWORK_FORCELOGIN;
		$error = 'abgemeldet';
	} # logout

	# check if there is a session
  $r = sql( '
		SELECT
			uid,
			sid
		FROM tbsession
		WHERE 
			uid = "'.$_SESSION['uid'].'" AND 
			sid = "'.$_SESSION['sid'].'" AND 
			browser = "'.substr( $request['browser'], 0, 255 ).'" AND
			ip = "'.$request['ip'].'"
		;' );
		
	# check if session is valid
	if( $session = mysqli_fetch_assoc( $r ) ) {
		
		# check if there is a login
		if( isset( $get['login'] ) && isset( $get['username'] ) && isset( $get['password'] ) && isset( $session['sid'] ) ) {

			# delete old session
			sql( 'DELETE FROM tbsession WHERE uid = "'.$session['uid'].'" AND sid = "'.$session['sid'].'";' );
			session_unset();
			unset( $session );

			# check if login is correct
			if( $session['uid'] = login( $get['username'], $get['password'] ) ) {
				# delete duplicate sessions of same user
				sql( 'DELETE FROM tbsession WHERE uid = "'.$session['uid'].'";' );
				# create new session
				$session['sid'] = md5( time().$session['uid'] );
				sql( 'INSERT INTO tbsession SET uid = "'.$session['uid'].'", sid = "'.$session['sid'].'";' );
				
				# delete login page
				unset( $get['page'] );
			} else {
				$error = 'Login fehlgeschlagen';
			} # if login is correct
			# if login was incorrect old session is already destroyed and will be recreated down below

		} # if login, username, password

	# session is invalid
	} else {
		unset( $session );
		if( !isset( $error ) ) $error = 'Session ist ungültig';
	} # if session is valid
		
	# no session exists
	if( !isset( $session['sid'] ) ) {
		# empty user object
		session_unset();
		unset( $user );
		$user['uid'] = 0;
	
		$session['uid'] = $user['uid'];
		$session['sid'] = md5( time().$user['uid'] );
		sql( 'INSERT INTO tbsession SET uid = "'.$session['uid'].'", sid = "'.$session['sid'].'";' );
	} # if no session

	# update session	
	sql( '
		INSERT INTO tbsession SET
			uid = "'.$session['uid'].'", 
			sid = "'.$session['sid'].'", 
			domain = "'.$request['domain'].'", 
			timestamp = UNIX_TIMESTAMP(), 
			page = "'.$get['page'].'", 
			url = "'.$request['url'].'", 
			browser = "'.$request['browser'].'", 
			ip = "'.$request['ip'].'",
			host = "'.$request['host'].'",
			referer = "'.$request['referer'].'"
		ON DUPLICATE KEY UPDATE
			domain = "'.$request['domain'].'", 
			timestamp = UNIX_TIMESTAMP(), 
			page = "'.$get['page'].'", 
			url = "'.$request['url'].'", 
			browser = "'.$request['browser'].'", 
			ip = "'.$request['ip'].'",
			host = "'.$request['host'].'",
			referer = "'.$request['referer'].'"
		;' );
	
	# load session data in array
	$r = sql( 'SELECT * FROM tbsession WHERE uid = "'.$session['uid'].'" AND sid = "'.$session['sid'].'";' );
	$session = mysqli_fetch_assoc( $r );

	$_SESSION['uid'] = $session['uid'];
	$_SESSION['sid'] = $session['sid'];
	
	# if logged in
	if( $session['uid'] > 0 ) {
		$user = getuserbyid( $session['uid'] );
		$session['login'] = true;

		# Berechtigungen einlesen
		$r = sql( 'SELECT privilege FROM tbpermission WHERE role = '.$user['role'].';' );
		while( $data = mysqli_fetch_assoc( $r ) ) {
			$session['permission'][$data['privilege']] = true;
		} # while fetch data
	} else {
		$session['login'] = false;
	}
	
## alte Sessions löschen
  sql( 'DELETE FROM tbsession WHERE timestamp < ( UNIX_TIMESTAMP() - '.FRAMEWORK_SESSIONTIMEOUTANON.' ) AND uid = 0;' );
  sql( 'DELETE FROM tbsession WHERE timestamp < ( UNIX_TIMESTAMP() - '.FRAMEWORK_SESSIONTIMEOUTUSER.' ) AND uid > 0;' );
  