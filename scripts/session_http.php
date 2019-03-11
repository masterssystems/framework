<?php
## Datenstruktur initialisieren #
  unset( $session );

  # Parameter für Sessionmanagement
  $gstime = 600;		# 600 Sek. = 5 Minuten - Guest Session Timeout
  $lstime = 3600;		# 3600 Sek. = 1 Stunde - Logged in Session Timeout

  if( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
    $session['username'] = $_SERVER['PHP_AUTH_USER'];

		# Userdaten einlesen
		$r = sql( '
			SELECT tbuser.* 
			FROM tbuser 
			INNER JOIN tbcompany
				ON tbuser.company = tbcompany.id
			WHERE tbuser.username = "'.$session['username'].'" 
				AND tbuser.valid_from <= NOW() 
				AND ( tbuser.valid_to > NOW() OR tbuser.valid_to = "0000-00-00" )
				AND tbcompany.valid_from <= NOW() 
				AND ( tbcompany.valid_to > NOW() OR tbcompany.valid_to = "0000-00-00" )
			;' );
		
		if( $user = mysqli_fetch_assoc( $r ) ) {
		  # update session
			sql( '
				INSERT INTO tbsession SET
					uid = "'.$user['uid'].'", 
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
			$r = sql( 'SELECT * FROM tbsession WHERE uid = "'.$user['uid'].'";' );
			$session = mysqli_fetch_assoc( $r );
			$session['login'] = true;

			# Berechtigungen einlesen
			$r = sql( 'SELECT privilege FROM tbpermission WHERE uid = '.$user['uid'].';' );
			while( $data = mysqli_fetch_assoc( $r ) ) {
				$session['permission'][$data['privilege']] = true;
			} # while fetch data
			
			# Ende eingeloggter Nutzer
		} else {
	    $session['login'] = false;
	    $_SERVER['PHP_AUTH_USER'] = null;
		} # if user found
  } else {
    $session['login'] = false;
  } # if PHPAUTHUSER set

	# Sessionlink erzeugen
	$session['link'] = '';

## alte Sessions löschen
	sql( 'DELETE FROM tbsession WHERE ( UNIX_TIMESTAMP() - timestamp > '.$gstime.' ) AND uid = 0;' );
	sql( 'DELETE FROM tbsession WHERE ( UNIX_TIMESTAMP() - timestamp > '.$lstime.' ) AND uid > 0;' );
	